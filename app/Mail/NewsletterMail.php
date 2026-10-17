<?php

namespace App\Mail;

use App\Models\User;
use App\Support\MailHtml;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectText,
        public string $content,
        public ?User $user = null
    ) {}

    public function build()
    {
        return $this
            ->subject($this->subjectText)
            ->view('emails.newsletter')
            ->with([
                'user' => $this->user,
                'body' => MailHtml::format($this->content),
            ]);
    }
}
