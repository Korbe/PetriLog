<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use App\Models\Ticket;
use App\Mail\TicketAdminMail;
use App\Mail\TicketUserMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ticket_create(): void
    {
        $response = $this->get(route('app.ticket.create'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_ticket_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('app.ticket.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Ticket/Create')
                ->etc()
        );
    }

    public function test_authenticated_user_can_submit_ticket_and_mails_are_sent(): void
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
            ->post(route('app.ticket.store'), $data);

        // Redirect + Flash
        $response->assertRedirect(route('app.dashboard'));
        $response->assertSessionHas('success', 'Ticket erfolgreich erstellt.');

        // Ticket in DB
        $this->assertDatabaseHas('tickets', [
            'title' => 'Fehler A',
            'user_id' => $user->id,
            'category' => 'ui',
        ]);

        $ticket = Ticket::where('title', 'Fehler A')->first();
        $this->assertNotNull($ticket);

        // Admin-Mail
        Mail::assertQueued(TicketAdminMail::class, function ($mail) use ($ticket, $user) {
            return $mail->hasTo('info@petrilog.com')
                && $mail->ticket->id === $ticket->id
                && $mail->user->id === $user->id;
        });

        // User-Mail (KEIN Ticket!)
        Mail::assertQueued(TicketUserMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->user->id === $user->id;
        });
    }

    public function test_store_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('app.ticket.store'), [
                'description' => 'Beschreibung',
            ]);

        $response->assertSessionHasErrors('title');
    }
}
