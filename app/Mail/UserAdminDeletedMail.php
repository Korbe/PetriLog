<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
            ->subject('Account gelöscht: ' . ($this->user['name'] ?? 'Unbekannt'))
            ->view('emails.user-admin-deleted');
    }
}
