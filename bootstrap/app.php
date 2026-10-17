<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdminMiddleware::class,
        ]);

        // Vor Session & Co.: während einer produktiven Wiederherstellung alle Anfragen abweisen
        $middleware->prepend(\App\Http\Middleware\BlockDuringRestore::class);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\TrackPageView::class,
            // IsAdminMiddleware::class
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Abgelaufenes CSRF-Token (z.B. Formular lange offen, Session weg): statt der 419-Seite zurück zum Formular
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($response->getStatusCode() === 419 && ! $request->expectsJson()) {
                return back()->with('error', 'Sitzung abgelaufen, bitte erneut versuchen.');
            }

            return $response;
        });
    })->create();
