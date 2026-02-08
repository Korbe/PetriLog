<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Controller;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->route('app.dashboard')->with('success', 'Deine E-Mail wurde erfolgreich bestätigt!');
    }
}
