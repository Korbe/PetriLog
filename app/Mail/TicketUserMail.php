<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class TicketUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($data)
    {
        $this->user = $data['user'];
    }

    public function build()
    {
        return $this
            ->subject("Ticket erhalten - wir kümmern uns darum!")
            ->view('emails.default')
            ->with([
                'name' => $this->user->name,
                'content' => new HtmlString('
                                            <p> vielen Dank für deine Anfrage. Wir haben dein Ticket erfolgreich erhalten und werden es so rasch wie möglich bearbeiten.</p> 
                                            <p> Falls weitere Informationen benötigt werden oder Rückfragen auftreten, melden wir uns direkt bei dir.</p> 
                                            <p> Bitte antworte nicht auf diese E-Mail - sie dient ausschließlich zur Bestätigung des Ticket-Eingangs.</p>
                                            '),
            ]);
    }
}
