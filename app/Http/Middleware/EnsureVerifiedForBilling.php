<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureVerifiedForBilling
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->hasVerifiedEmail()) {
            return redirect()->back()->with('error', 'Bitte verifiziere zuerst deine E-Mail.');
        }

        return $next($request);
    }
}
