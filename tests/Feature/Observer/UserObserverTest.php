<?php


namespace Tests\Feature\Observer;

use App\Mail\UserDeletedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Mockery;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_user_cancels_active_subscriptions(): void
    {
        $user = User::factory()->create();

        // Subscription erstellen (lokal, keine echte Stripe-Verbindung)
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'active',
            'stripe_id' => 'fake_id_123',
        ]);

        // Stripe-Aufrufe komplett mocken
        $mock = Mockery::mock($subscription)->makePartial();
        $mock->shouldReceive('cancelNow')->once()->andReturnUsing(function () use ($subscription) {
            $subscription->update([
                'ends_at' => now(),
                'stripe_status' => 'canceled',
            ]);
        });

        // Cache Observer aktualisieren, damit er das Mock verwendet
        $user->setRelation('subscriptions', collect([$mock]));

        $user->delete();

        $this->assertEquals('canceled', $subscription->fresh()->stripe_status);
        $this->assertNotNull($subscription->fresh()->ends_at);
    }

    public function test_deleted_user_receives_deleted_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Max Mustermann',
        ]);

        $user->delete(); // Observer::deleted()

        Mail::assertQueued(
            UserDeletedMail::class,
            function (UserDeletedMail $mail) use ($user) {
                return $mail->hasTo($user->email);
            }
        );
    }

    public function test_non_subscribed_user_does_not_cancel_anything(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->assertFalse($user->subscribed());

        // Löschen darf nicht crashen
        $user->delete();

        Mail::assertQueued(UserDeletedMail::class);
    }
}
