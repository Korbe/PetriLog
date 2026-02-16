<?php

namespace App\Observers;

use App\Mail\UserAdminDeletedMail;
use App\Mail\UserDeletedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function deleting(User $user): void
    {
        if ($user->subscribed()) {
            $user->subscriptions->each(fn($sub) => $sub->cancelNow());
        }
        
        $user->load('state');

        Mail::to('info@petrilog.com')->queue(
            new UserAdminDeletedMail([
                'name'  => $user->name,
                'email' => $user->email,
                'tel'   => $user->tel,
                'state' => $user->state?->name,
            ])
        );
    }

    public function deleted(User $user): void
    {
        Mail::to($user->email)->queue(new UserDeletedMail($user->name));
    }
}
