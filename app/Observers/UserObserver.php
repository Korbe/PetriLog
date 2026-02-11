<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function deleting(User $user): void
    {
        if ($user->subscribed()) {

            $user->subscriptions->each(fn($sub) => $sub->cancelNow());
            
            // Stripe Customer komplett löschen
            $user->deleteStripeCustomer();

        }
    }
}
