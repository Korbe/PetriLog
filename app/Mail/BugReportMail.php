<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BugReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Neuer Bug-Report')
            ->markdown('emails.bug-report')
            ->with([
                'user' => $this->data['user'],
                'bug'  => $this->data['bug'],
            ]);
    }
}
