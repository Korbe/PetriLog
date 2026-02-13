<?php

namespace Tests\Feature\Listener;

use Tests\TestCase;
use App\Listeners\SendNewUserEmail;
use App\Mail\NewUserMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendNewUserEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_queues_new_user_mail_when_user_registered()
    {
        // Mail-Faking aktivieren
        Mail::fake();

        // Neuen User erstellen
        $user = User::factory()->create();

        // Registered Event auslösen
        $event = new Registered($user);

        // Listener aufrufen
        $listener = new SendNewUserEmail();
        $listener->handle($event);

        // Prüfen, dass die Mail in die Queue gestellt wurde
        Mail::assertQueued(NewUserMail::class, function ($mail) use ($user) {
            return $mail->hasTo('info@petrilog.com') &&
                $mail->user->id === $user->id;
        });
    }
}
