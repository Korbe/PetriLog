<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;

class SendWelcomeMail
{
    public function handle(Registered $event)
    {
        Mail::to($event->user->email)->queue(new WelcomeMail($event->user->name));
    }
}
