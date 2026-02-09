<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BugReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $bug;
    public $user;

    public function __construct($data)
    {
        $this->bug = $data['bug'];
        $this->user = $data['user'];
    }

    public function build()
    {
        return $this->subject('Neuer Bug-Report')
            ->markdown('emails.bug-report')
            ->with([
                'bug'  => $this->bug,
                'user' => $this->user,
            ]);
    }
}