<?php

namespace Tests\Feature;

use App\Mail\CorrectionAdminMail;
use App\Mail\CorrectionReviewedMail;
use App\Models\Correction;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function lake(): Lake
    {
        $lake = Lake::factory()->create(['name' => 'Wörthersee']);
        $lake->states()->attach(State::factory()->create());

        return $lake;
    }

    public function test_user_can_report_an_error_and_team_and_editors_are_notified(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR, 'email' => 'editor@example.com']);
        $user = User::factory()->create();
        $lake = $this->lake();

        $this->actingAs($user)->post(route('app.corrections.store', ['type' => 'lake', 'id' => $lake->id]), [
            'category' => 'fish',
            'message' => 'Im See gibt es auch Zander.',
            'source' => 'https://fischereiverein.example',
        ])->assertSessionHas('success');

        $correction = Correction::sole();
        $this->assertSame('open', $correction->status);
        $this->assertTrue($correction->correctable->is($lake));
        $this->assertSame($user->id, $correction->user_id);

        Mail::assertQueued(CorrectionAdminMail::class, fn($mail) => $mail->hasTo('info@petrilog.com') && $mail->hasTo('editor@example.com'));
    }

    public function test_validation_and_fish_categories(): void
    {
        $user = User::factory()->create();
        $fish = Fish::factory()->create();

        // Fischarten haben keinen Fischbestand/Kartenverkauf
        $this->actingAs($user)->post(route('app.corrections.store', ['type' => 'fish', 'id' => $fish->id]), [
            'category' => 'tickets', 'message' => 'Stimmt nicht.',
        ])->assertSessionHasErrors('category');

        $this->actingAs($user)->post(route('app.corrections.store', ['type' => 'fish', 'id' => $fish->id]), [
            'category' => 'description', 'message' => '',
        ])->assertSessionHasErrors('message');

        $this->assertSame(0, Correction::count());
        auth()->logout();
        $this->post(route('app.corrections.store', ['type' => 'fish', 'id' => $fish->id]), [])->assertRedirect('/login');
    }

    public function test_spam_limit_per_user(): void
    {
        $user = User::factory()->create();
        $lake = $this->lake();
        $payload = ['category' => 'other', 'message' => 'Bitte prüfen.'];

        for ($i = 0; $i < Correction::MAX_OPEN_PER_USER; $i++) {
            $c = new Correction($payload);
            $c->correctable()->associate($lake);
            $c->user()->associate($user);
            $c->save();
        }

        $this->actingAs($user)->post(route('app.corrections.store', ['type' => 'lake', 'id' => $lake->id]), $payload)
            ->assertSessionHasErrors('message');
    }

    public function test_editor_can_review_and_user_is_informed(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR, 'name' => 'Erika']);
        $user = User::factory()->create();
        $correction = new Correction(['category' => 'tickets', 'message' => 'Tageskarte kostet 18 €.']);
        $correction->correctable()->associate($this->lake());
        $correction->user()->associate($user);
        $correction->save();

        $this->actingAs($editor)->get(route('admin.corrections.index'))
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/Corrections/Index')
                ->where('open.0.entry.name', 'Wörthersee')
                ->where('open.0.category', 'Kartenverkauf / Preise'));

        $this->actingAs($editor)->post(route('admin.corrections.resolve', $correction), [
            'status' => 'done', 'note' => 'Preis aktualisiert.',
        ])->assertSessionHas('success');

        $correction->refresh();
        $this->assertSame('done', $correction->status);
        $this->assertSame($editor->id, $correction->reviewed_by);
        Mail::assertQueued(CorrectionReviewedMail::class, fn($mail) => $mail->hasTo($user->email));

        // Kein zweites Bearbeiten
        $this->actingAs($editor)->post(route('admin.corrections.resolve', $correction), ['status' => 'rejected'])
            ->assertSessionHas('error');
        $this->assertSame('done', $correction->fresh()->status);
    }

    public function test_regular_users_cannot_review(): void
    {
        $correction = new Correction(['category' => 'other', 'message' => 'Test']);
        $correction->correctable()->associate($this->lake());
        $correction->save();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.corrections.resolve', $correction), ['status' => 'done'])
            ->assertRedirect('/dashboard');
        $this->assertSame('open', $correction->fresh()->status);
    }

    public function test_mails_render(): void
    {
        $correction = new Correction(['category' => 'fish', 'message' => "Zander fehlt.\nHecht auch.", 'source' => 'https://verein.example']);
        $correction->correctable()->associate($this->lake());
        $correction->user()->associate(User::factory()->create(['name' => 'Max']));
        $correction->save();

        $admin = (new CorrectionAdminMail($correction))->render();
        $this->assertStringContainsString('Wörthersee', $admin);
        $this->assertStringContainsString('Zander fehlt.', $admin);
        $this->assertStringContainsString('https://verein.example', $admin);

        $correction->forceFill(['status' => 'done', 'review_note' => 'Ist eingetragen.'])->save();
        $user = (new CorrectionReviewedMail($correction))->render();
        $this->assertStringContainsString('ist korrigiert', $user);
        $this->assertStringContainsString('Ist eingetragen.', $user);
    }
}
