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
        return $this->subject('Neues Ticket')
            ->markdown('emails.admin-ticket')
            ->with([
                'ticket'  => $this->ticket,
                'user' => $this->user,
            ]);
    }
}