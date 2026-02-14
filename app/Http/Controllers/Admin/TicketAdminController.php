<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Ticket;
use App\Http\Controllers\Controller;

class TicketAdminController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'title' => $ticket->title,
                    'category' => $ticket->category,
                    'user' => $ticket->user ? $ticket->user->name : 'Unbekannt',
                    'email' => $ticket->user ? $ticket->user->email : 'Unbekannt',
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                    'url' => route('admin.tickets.show', $ticket->id),
                ];
            }),
        ]);
    }

    public function show(Ticket $ticket)
    {
        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'category' => $ticket->category,
                'steps' => $ticket->steps,
                'url' => $ticket->url,
                'user' => $ticket->user ? $ticket->user->name : 'Unbekannt',
                'email' => $ticket->user ? $ticket->user->email : 'Unbekannt',
                'created_at' => $ticket->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket gelöscht.');
    }
}
