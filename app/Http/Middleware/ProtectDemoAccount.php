<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Der öffentliche Demo-Zugang teilen sich viele Besucher: Profil, Passwort, Sicherheit, Konto und Abo
 * dürfen dort nicht verändert werden (sonst sperrt ein Besucher alle anderen aus).
 */
class ProtectDemoAccount
{
    public const MESSAGE = 'Im Demo-Zugang kann das Profil nicht geändert werden.';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isDemo() || ! $this->isProtected($request)) {
            return $next($request);
        }

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => self::MESSAGE], 403);
        }

        return redirect()->back(fallback: route('app.profile.show'))->with('error', self::MESSAGE);
    }

    private function isProtected(Request $request): bool
    {
        // Abo-Verwaltung (Spark) – auch nicht ansehen, dort ließe sich das Abo kündigen
        if ($request->is('billing', 'billing/*', 'billing-portal', 'spark/*')) {
            return true;
        }

        // Profil, Passwort, 2FA, Browsersitzungen, Profilfoto, Bundesland, Newsletter, Konto löschen
        return ! $request->isMethodSafe()
            && $request->is('user', 'user/*')
            && ! $request->is('user/confirm-password');
    }
}
