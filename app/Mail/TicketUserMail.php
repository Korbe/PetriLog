<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public ?Ticket $ticket;

    public function __construct($data)
    {
        $this->user = $data['user'];
        $this->ticket = $data['ticket'] ?? null;
    }

    public function build()
    {
        return $this
            ->subject('Ticket erhalten – wir kümmern uns darum')
            ->view('emails.ticket-user')
            ->with([
                'category' => $this->ticket?->categoryLabel(),
            ]);
    }
}
