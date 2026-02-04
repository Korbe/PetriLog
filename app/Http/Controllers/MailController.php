<?php

namespace App\Http\Controllers;

use App\Mail\NewUserMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendTestMail(Request $request)
    {
        $user = Auth::user();

        Mail::to('info@korbitsch.at')->send(new NewUserMail($user));
        Mail::to('info@petrilog.com')->send(new NewUserMail($user));

        return response()->json([
            'success' => true,
            'message' => 'Mail erfolgreich gesendet'
        ]);
    }
}
