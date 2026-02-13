<?php

namespace Tests\Feature\Listener;

use App\Listeners\SendWelcomeMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendWelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_event_queues_welcome_mail(): void
    {
        // Mail faken
        Mail::fake();

        // Einen User erstellen
        $user = User::factory()->create([
            'name' => 'Max Mustermann',
            'email' => 'max@test.com',
        ]);

        // Event erstellen
        $event = new Registered($user);

        // Listener aufrufen
        $listener = new SendWelcomeMail();
        $listener->handle($event);

        // Prüfen, ob Mail in die Queue gelegt wurde
        Mail::assertQueued(WelcomeMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->name === $user->name;
        });
    }
}
