<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

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
            ->view('emails.default')
            ->with([
                'name' => 'Team',
                'content' => new HtmlString('
                <h1>Neues Ticket</h1>

                <p>
                    <strong>Von:</strong>
                    ' . $this->user->name . ' (' . $this->user->email . ') (' . $this->user->tel . ')
                </p>

                <p>
                    <strong>Titel:</strong>
                    ' . $this->ticket->title . '<br>
                    <strong>Kategorie:</strong>
                    ' . $this->ticket->category . '
                </p>

                <h3>Beschreibung</h3>
                <p>
                    ' . ($this->ticket->description ?? 'Keine Beschreibung') . '
                </p>

                <h3>Schritte zur Reproduktion</h3>
                <p>
                    ' . $this->ticket->steps . '
                </p>

                <h3>URL</h3>
                <p>
                    <a href="' . $this->ticket->url . '">' . $this->ticket->url . '</a>
                </p>
            '),
            ]);
    }
}
