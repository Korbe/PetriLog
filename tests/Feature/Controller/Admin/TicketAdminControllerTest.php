<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_or_non_admin_cannot_access_tickets(): void
    {
        $ticket = Ticket::factory()->create();

        // Guest
        $response = $this->get(route('admin.tickets.index'));
        $response->assertRedirect('/login');

        // Normaler User
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get(route('admin.tickets.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('app.dashboard'));
        $response->assertSessionHas('error', 'Du hast keinen Zugriff.'); 
    }

    public function test_admin_can_view_tickets_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $ticket = Ticket::factory()->create([
            'title' => 'Fehler A',
            'category' => 'UI',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.tickets.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Admin/Tickets/Index')
                ->has(
                    'tickets',
                    1,
                    fn($page) => $page
                        ->where('id', $ticket->id)
                        ->where('title', 'Fehler A')
                        ->where('category', 'UI')
                        ->where('user', $ticket->user ? $ticket->user->name : 'Unbekannt')
                        ->etc()
                )
        );
    }

    public function test_admin_can_view_single_ticket(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Ticket mit User
        $user = User::factory()->create(['name' => 'Max Mustermann']);

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'title' => 'Fehler B',
            'description' => 'Beschreibung B',
            'category' => 'Backend',
            'steps' => 'Schritt 1, 2',
            'url' => 'https://example.test',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket->id));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Admin/Tickets/Show')
                ->where('ticket.id', $ticket->id)
                ->where('ticket.title', 'Fehler B')
                ->where('ticket.description', 'Beschreibung B')
                ->where('ticket.category', 'Backend')
                ->where('ticket.steps', 'Schritt 1, 2')
                ->where('ticket.url', 'https://example.test')
                ->where('ticket.user', $user->name)
                ->where('ticket.email', $user->email)
                ->etc()
        );
    }


    public function test_admin_can_delete_ticket(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.tickets.destroy', $ticket->id));

        $response->assertRedirect(route('admin.tickets.index'));
        $response->assertSessionHas('success', 'Ticket gelöscht.');

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }
}
