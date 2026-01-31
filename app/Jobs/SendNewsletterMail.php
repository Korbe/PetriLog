<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\NewsletterMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $subject,
        public string $content
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->send(new NewsletterMail(
                $this->subject,
                $this->content,
                $this->user
            ));
    }
}
