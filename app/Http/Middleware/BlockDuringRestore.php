<?php

namespace App\Http\Middleware;

use App\Services\Backup\RestoreMaintenance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Während einer produktiven Wiederherstellung keine Anfragen an App und Datenbank durchlassen.
 * Läuft vor der Session-Middleware, weil auch die Sitzungstabelle gerade ersetzt wird.
 */
class BlockDuringRestore
{
    public function handle(Request $request, Closure $next): Response
    {
        $restore = RestoreMaintenance::active();

        if ($restore === null || $request->is('up')) {
            return $next($request);
        }

        return response()->view('maintenance-restore', ['restore' => $restore], 503, ['Retry-After' => '30']);
    }
}
