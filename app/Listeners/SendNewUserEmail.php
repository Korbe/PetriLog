<?php

namespace App\Listeners;

use App\Mail\NewUserMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendNewUserEmail
{
    public function handle(Registered $event)
    {
        Mail::to('info@petrilog.com')->queue(new NewUserMail($event->user));
    }
}
