<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class NewUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function build()
    {
        $this->user->load('state');

        return $this->subject('Neuer User bei PetriLog!')
            ->view('emails.default')
            ->with([
                'name' => 'Team',
                'content' => new HtmlString('
                <h1>Neuer User!</h1>
                <p>
                    <strong>Name:</strong> ' . $this->user->name . '<br>
                    <strong>E-Mail:</strong> ' . $this->user->email . '<br>
                    <strong>Tel:</strong> ' . $this->user->tel . '<br>
                    <strong>Bundesland:</strong> ' . $this->user->state?->name ?? '—' . '
                </p>
            ')
            ]);
    }
}
