<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\HtmlString;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectText,
        public string $content,
        public User $user
    ) {}

    public function build()
    {
        return $this
            ->subject($this->subjectText)
            ->markdown('emails.newsletter')
            ->with([
                'user' => $this->user,
                'content' => new HtmlString($this->content),
            ]);
    }
}
