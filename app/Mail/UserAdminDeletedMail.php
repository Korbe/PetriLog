<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class UserAdminDeletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this
            ->subject('User hat sich gelöscht!')
            ->view('emails.default')
            ->with([
                'name' => 'Team',
                'content' => new HtmlString('
                    <h1>User gelöscht</h1>

                    <p>
                        Ein User hat seinen Account selbstständig gelöscht.
                        Nachfolgend die gespeicherten Informationen:
                    </p>

                    <p>
                        <strong>Name:</strong> ' . e($this->user['name']) . '<br>
                        <strong>E-Mail:</strong> ' . e($this->user['email']) . '<br>
                        <strong>Telefon:</strong> ' . e($this->user['tel'] ?? '—') . '<br>
                        <strong>Bundesland:</strong> ' . e($this->user['state'] ?? '—') . '
                    </p>
                ')
            ]);
    }
}
