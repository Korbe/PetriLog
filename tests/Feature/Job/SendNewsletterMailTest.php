<?php

namespace Tests\Feature\Job;

use Tests\TestCase;
use App\Models\User;
use App\Jobs\SendNewsletterMail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendNewsletterMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsletter_job_is_dispatched_and_sends_mail()
    {
        Mail::fake();
        Queue::fake();

        // Benutzer erstellen
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $subject = 'Test Newsletter';
        $content = 'Das ist der Inhalt des Newsletters';

        // Job dispatchen
        SendNewsletterMail::dispatch($user, $subject, $content);

        // Prüfen, dass der Job in die Queue gestellt wurde
        Queue::assertPushed(SendNewsletterMail::class, function ($job) use ($user, $subject, $content) {
            return $job->user->id === $user->id
                && $job->subject === $subject
                && $job->content === $content;
        });

        // Job ausführen
        $job = new SendNewsletterMail($user, $subject, $content);
        $job->handle();

        // Prüfen, dass Mail gesendet wurde
        Mail::assertSent(NewsletterMail::class, function ($mail) use ($user) {
            // Nur die Empfänger prüfen
            return $mail->hasTo($user->email);
        });
    }
}
