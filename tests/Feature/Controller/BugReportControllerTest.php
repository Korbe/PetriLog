<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use App\Models\BugReport;
use App\Mail\BugReportMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BugReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_bugreport_create(): void
    {
        $response = $this->get(route('app.bug-report.create'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_bugreport_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('app.bug-report.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('BugReport/Create')
                ->etc()
        );
    }

    public function test_authenticated_user_can_submit_bugreport_and_mail_is_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $data = [
            'title' => 'Fehler A',
            'description' => 'Beschreibung A',
            'steps' => 'Schritt 1, Schritt 2',
            'url' => 'https://example.test',
            'category' => 'ui',
        ];

        $response = $this->actingAs($user)
            ->post(route('app.bug-report.store'), $data);

        $response->assertRedirect(route('app.bug-report.create'));
        $response->assertSessionHas('success', 'Fehlerbericht erfolgreich eingetragen.');

        $this->assertDatabaseHas('bug_reports', [
            'title' => 'Fehler A',
            'user_id' => $user->id,
        ]);

        // Prüft, dass eine Mail gesendet wurde
        Mail::assertQueued(BugReportMail::class, function ($mail) use ($user) {
            return $mail->hasTo('info@petrilog.com') &&
                $mail->bug->title === 'Fehler A' &&
                $mail->user->id === $user->id;
        });
    }

    public function test_store_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('app.bug-report.store'), [
                'description' => 'Beschreibung',
            ]);

        $response->assertSessionHasErrors('title');
    }
}
