<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionCreatedMail;
use Spark\Events\SubscriptionCreated;

class SendSubscriptionCreatedMail
{
    public function handle(SubscriptionCreated $event): void
    {
        $user = $event->billable;
        Mail::to($user->email)->send(new SubscriptionCreatedMail($user));
    }
}
