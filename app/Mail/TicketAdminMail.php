<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $user;

    public function __construct($data)
    {
        $this->ticket = $data['ticket'];
        $this->user = $data['user'];
    }

    public function build()
    {
        // Beschreibung/Schritte sind beim Speichern bereinigt (Ticket-Mutator), alles andere escaped die View
        return $this->subject('Neues Ticket: ' . $this->ticket->title)
            ->view('emails.ticket-admin')
            ->with([
                'category' => $this->ticket->categoryLabel(),
            ]);
    }
}
