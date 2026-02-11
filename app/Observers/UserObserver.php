<?php

namespace App\Observers;

use App\Models\User;
use App\Mail\UserDeletedMail;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function deleting(User $user): void
    {
        if ($user->subscribed()) {
            $user->subscriptions->each(fn($sub) => $sub->cancelNow());
        }
    }

    public function deleted(User $user) : void
    {
        Mail::to($user->email)->queue(new UserDeletedMail($user->name));
    }
}
