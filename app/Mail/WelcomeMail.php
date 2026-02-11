<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\HtmlString;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function build()
    {
        return $this
            ->subject("Herzlich Willkommen!")
            ->view('emails.default')
            ->with([
                'name' => $this->name,
                'content' => new HtmlString('
                                    <p>
                                        <strong>Willkommen bei PetriLog!</strong><br>
                                        Wir freuen uns sehr, dass du dich registriert hast und Teil unserer Angel-Community bist.
                                    </p>

                                    <p>
                                        Du kannst jetzt deine Fänge dokumentieren, deine Lieblingsfische verfolgen und spannende Angel-Abenteuer teilen.
                                    </p>
                         '),
            ]);
    }
}
