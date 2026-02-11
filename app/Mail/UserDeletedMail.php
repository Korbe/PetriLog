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
                                                Schade, dass du uns verlässt. Wir möchten uns <strong>herzlich bei dir bedanken</strong>,
                                                dass du Teil unserer Plattform warst.
                                            </p>

                                            <p>
                                                Dein Account wurde <strong>erfolgreich gelöscht</strong>.<br>
                                                Alle <strong>aktiven Abonnements</strong> wurden selbstverständlich beendet.
                                            </p>

                                            <p>
                                                Falls du dich irgendwann entscheidest zurückzukommen,
                                                bist du <strong>jederzeit wieder herzlich willkommen</strong>.
                                            </p>

                                            <p>
                                                Wir wünschen dir <strong>alles Gute</strong> und viel Erfolg bei deinen Angelausflügen.
                                            </p>
                                            '),
            ]);
    }
}
