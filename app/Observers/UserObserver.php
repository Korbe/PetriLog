<?php

namespace App\Observers;

use App\Models\User;
use Laravel\Cashier\Cashier;

class UserObserver
{
    public function deleting(User $user): void
    {
        if ($user->subscribed()) {

            $user->subscriptions->each(fn($sub) => $sub->cancelNow());
            
            // Stripe Customer komplett löschen
            Cashier::stripe()->customers->delete($user->stripe_id);

        }
    }
}
