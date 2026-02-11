<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\HtmlString;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserDeletedMail extends Mailable implements ShouldQueue
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
            ->subject("Schade, dass du uns verlässt!")
            ->view('emails.default')
            ->with([
                'name' => $this->name,
                'content' => new HtmlString('
                                            <p>
                                            schade, dass du uns verlässt. Wir möchten uns herzlich bei dir bedanken,
                                            dass du Teil unserer Plattform warst.
                                            </p>

                                            <p>
                                            Dein Account wurde erfolgreich gelöscht.
                                            Alle aktiven Abonnements wurden selbstverständlich beendet.
                                            </p>

                                            <p>
                                            Falls du dich irgendwann entscheidest zurückzukommen,
                                            bist du jederzeit wieder herzlich willkommen.
                                            </p>

                                            <p>
                                            Wir wünschen dir alles Gute und viel Erfolg bei deinen Angelausflügen.
                                            </p>
                                            '),
            ]);
    }
}
