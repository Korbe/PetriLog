<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * „Demo ansehen“ auf der Login-Seite: meldet ohne Passwort im öffentlichen Demo-Konto an.
 * Profil, Passwort, Konto und Abo sind dort gesperrt (ProtectDemoAccount).
 */
class DemoLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $demo = config('app.demo_login') ? User::where('email', User::DEMO_EMAIL)->first() : null;

        if (! $demo) {
            return back()->withErrors(['email' => 'Die Demo ist gerade nicht verfügbar.']);
        }

        Auth::login($demo);
        $request->session()->regenerate();

        return redirect()->route('app.dashboard')
            ->with('success', 'Willkommen in der Demo! Schau dich um – Änderungen am Profil sind hier gesperrt.');
    }
}
