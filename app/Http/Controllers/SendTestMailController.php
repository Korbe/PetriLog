<?php

namespace App\Http\Controllers;

use App\Mail\NewUserMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendTestMailController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user(); // oder ein anderer User

        Mail::to('info@petrilog.com')
            ->send(new NewUserMail($user));
        Mail::to('info@korbitsch.at')
            ->send(new NewUserMail($user));

        return back()->with('success', 'Mail wurde versendet');
    }
}
