<?php

namespace Tests\Feature\Controller;

use App\Mail\SubmissionAdminMail;
use App\Mail\SubmissionReviewedMail;
use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function pendingFish(User $user, string $name = 'Marmorierte Forelle'): Fish
    {
        $fish = Fish::factory()->create(['name' => $name]);
        $fish->forceFill(['is_approved' => false, 'submitted_by' => $user->id])->save();

        return $fish;
    }

    public function test_user_can_submit_a_fish_that_is_pending_and_escaped(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('app.submissions.store', 'fish'), [
                'name' => '  Marmorierte   Forelle ',
                'desc' => "Kommt im Isonzo vor.\n<script>alert(1)</script>",
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'Marmorierte Forelle');

        $fish = Fish::where('name', 'Marmorierte Forelle')->sole();
        $this->assertFalse($fish->is_approved);
        $this->assertSame($user->id, $fish->submitted_by);
        $this->assertStringNotContainsString('<script>', $fish->desc);
        $this->assertStringContainsString('&lt;script&gt;', $fish->desc);
        $this->assertStringContainsString('<br>', $fish->desc);
    }

    public function test_lake_submission_requires_a_state_and_stores_it(): void
    {
        $user = User::factory()->create();
        $state = State::factory()->create();

        $this->actingAs($user)
            ->postJson(route('app.submissions.store', 'lake'), ['name' => 'Schwarzsee'])
            ->assertJsonValidationErrors('states');

        $this->actingAs($user)
            ->postJson(route('app.submissions.store', 'lake'), [
                'name' => 'Schwarzsee', 'states' => [$state->id], 'ticket_sales' => 'Trafik am Hauptplatz',
            ])
            ->assertCreated();

        $lake = Lake::where('name', 'Schwarzsee')->sole();
        $this->assertFalse($lake->is_approved);
        $this->assertEquals([$state->id], $lake->states()->pluck('states.id')->all());
    }

    public function test_duplicates_of_existing_entries_are_rejected(): void
    {
        $user = User::factory()->create();
        Fish::factory()->create(['name' => 'Hecht']);

        $this->actingAs($user)
            ->postJson(route('app.submissions.store', 'fish'), ['name' => 'hecht'])
            ->assertJsonValidationErrors('name');
    }

    public function test_pending_entries_are_only_visible_to_their_submitter(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $fish = $this->pendingFish($owner);

        $this->actingAs($other)->get(route('app.fish.show', $fish))->assertNotFound();
        $this->actingAs($other)->get(route('app.fish.index'))
            ->assertInertia(fn(Assert $page) => $page->where('fish', fn($list) => collect($list)->doesntContain('id', $fish->id)));

        $this->actingAs($owner)->get(route('app.catched.create'))
            ->assertInertia(fn(Assert $page) => $page->where('fish', fn($list) => collect($list)->contains('name', 'Marmorierte Forelle (in Prüfung)')));
        $this->actingAs($other)->get(route('app.catched.create'))
            ->assertInertia(fn(Assert $page) => $page->where('fish', fn($list) => collect($list)->doesntContain('id', $fish->id)));
    }

    public function test_foreign_pending_entries_cannot_be_used_for_a_catch(): void
    {
        $other = User::factory()->create();
        $fish = $this->pendingFish(User::factory()->create());

        $this->actingAs($other)
            ->post(route('app.catched.store'), [
                'date' => '2026-05-01', 'fish_id' => $fish->id, 'lake_id' => Lake::factory()->create()->id,
                'length' => 40, 'latitude' => 47.8, 'longitude' => 13.0,
            ])
            ->assertSessionHasErrors('fish_id');

        $this->assertSame(0, Catched::count());
    }

    public function test_admin_can_approve_a_submission(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $fish = $this->pendingFish(User::factory()->create());

        $this->actingAs($admin)->get(route('admin.submissions.index'))
            ->assertInertia(fn(Assert $page) => $page->component('Admin/Submissions/Index')->has('submissions', 1));

        $this->actingAs($admin)
            ->post(route('admin.submissions.approve', ['type' => 'fish', 'id' => $fish->id]))
            ->assertRedirect();

        $this->assertTrue($fish->fresh()->is_approved);
        $this->actingAs(User::factory()->create())->get(route('app.fish.show', $fish))->assertOk();
    }

    public function test_rejecting_moves_catches_to_the_replacement_and_deletes_the_submission(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $fish = $this->pendingFish($owner, 'Hächt');
        $official = Fish::factory()->create(['name' => 'Hecht']);
        $catched = Catched::factory()->forUser($owner)->create([
            'fish_id' => $fish->id, 'lake_id' => Lake::factory()->create()->id, 'river_id' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.submissions.reject', ['type' => 'fish', 'id' => $fish->id]), ['replacement_id' => $official->id])
            ->assertRedirect();

        $this->assertModelMissing($fish);
        $this->assertSame($official->id, $catched->fresh()->fish_id);
    }

    public function test_submission_notifies_the_admin_team(): void
    {
        Mail::fake();
        $user = User::factory()->create(['name' => 'Max']);
        $state = State::factory()->create(['name' => 'Kärnten']);

        $this->actingAs($user)->postJson(route('app.submissions.store', 'river'), [
            'name' => 'Gurk', 'states' => [$state->id], 'desc' => 'Unterlauf',
        ])->assertCreated();

        Mail::assertQueued(SubmissionAdminMail::class, fn(SubmissionAdminMail $mail) => $mail->hasTo('info@petrilog.com')
            && $mail->submission['label'] === 'Fluss'
            && $mail->submission['states'] === ['Kärnten']
            && $mail->submission['submitter_email'] === $user->email);
    }

    public function test_approval_notifies_the_submitter_with_a_wiki_link(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $fish = $this->pendingFish($owner);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.submissions.approve', ['type' => 'fish', 'id' => $fish->id]));

        Mail::assertQueued(SubmissionReviewedMail::class, fn(SubmissionReviewedMail $mail) => $mail->hasTo($owner->email)
            && $mail->review['approved'] === true
            && $mail->review['url'] === route('app.fish.show', $fish));
    }

    public function test_rejection_notifies_the_submitter_with_reason_and_replacement(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $fish = $this->pendingFish($owner, 'Hächt');
        $official = Fish::factory()->create(['name' => 'Hecht']);
        Catched::factory()->forUser($owner)->create([
            'fish_id' => $fish->id, 'lake_id' => Lake::factory()->create()->id, 'river_id' => null,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.submissions.reject', ['type' => 'fish', 'id' => $fish->id]), [
                'replacement_id' => $official->id, 'reason' => 'Gibt es bereits als „Hecht“.',
            ]);

        Mail::assertQueued(SubmissionReviewedMail::class, fn(SubmissionReviewedMail $mail) => $mail->hasTo($owner->email)
            && $mail->review['approved'] === false
            && $mail->review['name'] === 'Hächt'
            && $mail->review['replacement'] === 'Hecht'
            && $mail->review['reason'] === 'Gibt es bereits als „Hecht“.');
    }

    public function test_submission_mails_render_and_escape_user_input(): void
    {
        $xss = '<img src=x onerror=alert(1)>';

        $admin = (new SubmissionAdminMail([
            'label' => 'See', 'name' => "Schwarzsee {$xss}", 'desc' => '<p>' . e($xss) . '</p>',
            'fishing_rights' => null, 'ticket_sales' => '<p>Trafik</p>', 'states' => ['Tirol'],
            'submitter_name' => $xss, 'submitter_email' => 'max@example.com', 'created_at' => '16.10.2026, 20:00 Uhr',
        ]))->render();

        $this->assertStringContainsString('Schwarzsee', $admin);
        $this->assertStringContainsString('Tirol', $admin);
        $this->assertStringContainsString(route('admin.submissions.index'), $admin);
        $this->assertStringNotContainsString('<img src=x', $admin);

        $approved = (new SubmissionReviewedMail([
            'user_name' => 'Max', 'label' => 'Fischart', 'name' => 'Huchen', 'approved' => true,
            'url' => 'https://petrilog.com/fish/1', 'replacement' => null, 'reason' => null,
        ]))->render();
        $this->assertStringContainsString('ist jetzt offiziell', $approved);
        $this->assertStringContainsString('https://petrilog.com/fish/1', $approved);

        $rejected = (new SubmissionReviewedMail([
            'user_name' => 'Max', 'label' => 'Fischart', 'name' => 'Hächt', 'approved' => false,
            'url' => null, 'replacement' => 'Hecht', 'reason' => $xss,
        ]))->render();
        $this->assertStringContainsString('„Hecht“', $rejected);
        $this->assertStringNotContainsString('<img src=x', $rejected);
    }

    public function test_regular_users_cannot_review_submissions(): void
    {
        $fish = $this->pendingFish(User::factory()->create());

        // Die isAdmin-Middleware leitet Nicht-Admins um
        $this->actingAs(User::factory()->create())
            ->post(route('admin.submissions.approve', ['type' => 'fish', 'id' => $fish->id]))
            ->assertRedirect();

        $this->assertFalse($fish->fresh()->is_approved);
    }
}
