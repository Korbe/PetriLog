<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\TicketAdminMail;
use App\Mail\TicketUserMail;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;


class TicketController extends Controller
{
    public function create()
    {
        return Inertia::render('Ticket/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'nullable|string',
            'url' => 'nullable|string',
            'category' => 'nullable|string|in:ui,functionality,performance,error,auth,abo,data_issue,feature_request,other',
        ]);

        $user = Auth::user();
        $data['user_id'] = $user->id;

        $ticket = Ticket::create($data);

        Mail::to('info@petrilog.com')->queue(new TicketAdminMail([
            'ticket' => $ticket,
            'user' => $user,
        ]));


        Mail::to($user->email)->queue(new TicketUserMail([
            'user' => $user,
        ]));

        return redirect()->route('app.dashboard')->with('success', 'Ticket erfolgreich erstellt.');
    }
}
