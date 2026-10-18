<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Zugang zum Admin-Bereich nach Rolle.
 *
 *   isAdmin        → nur Admins (Development, Benutzer löschen, Rollen, Pulse)
 *   isAdmin:staff  → Admins und Editoren
 *
 * Während einer Übernahme („Als Benutzer anmelden“) zählt die Rolle der Person, die übernommen hat –
 * so funktioniert das Beenden der Übernahme, ohne dem übernommenen Konto Rechte zu geben.
 */
class IsAdminMiddleware
{
    public function handle(Request $request, Closure $next, string $level = 'admin'): Response
    {
        $actor = $this->actor();

        $allowed = $actor && ($level === 'staff' ? $actor->isStaff() : $actor->isAdmin());

        if (! $allowed) {
            return redirect('/dashboard')->with('error', 'Du hast keinen Zugriff.');
        }

        return $next($request);
    }

    private function actor(): ?User
    {
        $user = Auth::user();

        if ($user && ($impersonatorId = session('impersonated_by'))) {
            return User::find($impersonatorId);
        }

        return $user;
    }
}
