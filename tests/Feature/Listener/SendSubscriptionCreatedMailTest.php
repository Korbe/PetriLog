<?php

namespace Tests\Feature\Listener;

use App\Listeners\SendSubscriptionCreatedMail;
use App\Mail\SubscriptionCreatedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Subscription;
use Spark\Events\SubscriptionCreated;
use Tests\TestCase;

class SendSubscriptionCreatedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_sends_subscription_created_mail_to_user()
    {
        Mail::fake();

        // User erstellen
        $user = User::factory()->create([
            'email' => 'testuser@example.com'
        ]);

        // Fake Subscription erstellen
        $subscription = new Subscription([
            'name' => 'default',
            'stripe_id' => 'sub_test_123',
            'stripe_status' => 'active',
            'user_id' => $user->id,
        ]);

        // SubscriptionCreated Event simulieren
        $event = new SubscriptionCreated($user, $subscription);

        // Listener direkt aufrufen
        $listener = new SendSubscriptionCreatedMail();
        $listener->handle($event);

        // Prüfen, dass Mail gesendet wurde
        Mail::assertSent(SubscriptionCreatedMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) &&
                $mail->user->id === $user->id;
        });
    }
}
