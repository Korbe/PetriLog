<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Cookielose Zugriffsstatistik: zählt Seitenaufrufe (auch Inertia-Navigationen) ohne IP-Adressen zu speichern.
 * Besucher werden nur über einen täglich wechselnden Hash unterschieden.
 */
class TrackPageView
{
    private const IGNORED_PATHS = ['admin*', 'pulse*', 'up', 'manifest.json', 'sw.js', 'build/*', 'storage/*',
        'report/export', 'report/print', 'report/pdf', 'billing*', 'stripe/*', 'spark/*', 'livewire/*', '_ignition/*', 'sanctum/*'];

    // Bots, Crawler, Link-Vorschauen (WhatsApp, Facebook …), Sicherheits-Scanner der Mail-Anbieter, HTTP-Bibliotheken
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless|lighthouse|uptime'
        . '|scan|check|fetch|facebookexternalhit|whatsapp|telegram|skype|slack|discord|embedly|outlook|office|'
        . 'googleimageproxy|ggpht|yahoo|petal|semrush|ahrefs|mj12|dotbot|seznam|bingpreview|go-http|java\\/|okhttp|'
        . 'axios|node|httpclient|libwww|guzzle|postman|insomnia|http_request/i';

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Läuft nach dem Senden der Antwort – kostet den User keine Ladezeit.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldTrack($request, $response)) {
            return;
        }

        try {
            $today = now('Europe/Vienna')->toDateString();

            PageView::create([
                'date' => $today,
                'route' => $request->route()?->getName(),
                'path' => mb_substr('/' . ltrim($request->path(), '/'), 0, 191),
                'visitor' => substr(hash_hmac('sha256', $request->ip() . '|' . $request->userAgent() . '|' . $today, config('app.key')), 0, 16),
                'authenticated' => $request->user() !== null,
                'referrer_host' => $this->externalReferrer($request),
                'device' => $this->device($request),
                'standalone' => $this->isStandalone($request),
            ]);
        } catch (Throwable $e) {
            Log::warning("Page view tracking failed: {$e->getMessage()}");
        }
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return false;
        }

        // Nur ganze Seiten bzw. Inertia-Seitenwechsel – keine Partial Reloads, Prefetches oder JSON-Abfragen
        $isInertia = $request->hasHeader('X-Inertia');
        if ($request->ajax() && ! $isInertia) {
            return false;
        }
        if ($request->hasHeader('X-Inertia-Partial-Data') || $request->header('Purpose') === 'prefetch'
            || $request->hasHeader('X-Inertia-Prefetch')) {
            return false;
        }

        if ($request->is(...self::IGNORED_PATHS)) {
            return false;
        }

        // Impersonierende Admins verfälschen die Statistik
        if ($request->user()?->isImpersonated()) {
            return false;
        }

        return ! self::isLikelyBot($request);
    }

    /**
     * Automatische Aufrufe erkennen. Sicherheits-Scanner der Mail-Anbieter (z. B. Outlook Safe Links) öffnen jeden Link
     * in einer Mail mit einer normalen Browser-Kennung – sie senden aber in der Regel keine Sprach-Präferenz.
     * Echte Browser schicken Accept-Language bei jedem Seitenaufruf und jeder Inertia-Navigation mit.
     */
    public static function isLikelyBot(Request $request): bool
    {
        $agent = (string) $request->userAgent();

        return $agent === ''
            || trim((string) $request->header('Accept-Language')) === ''
            || (bool) preg_match(self::BOT_PATTERN, $agent);
    }

    /**
     * Handy, Tablet oder Desktop – nur aus dem, was jeder Browser ohnehin mitschickt (User-Agent, Client Hint).
     * Bewusst kein Auslesen von Geräteinfos per Script (§ 165 TKG). iPads melden sich als Mac und zählen als Desktop.
     */
    public static function deviceFor(?string $userAgent, ?string $mobileHint = null): string
    {
        $ua = (string) $userAgent;

        return match (true) {
            (bool) preg_match('/iPad|Tablet|PlayBook|Silk|Kindle/i', $ua),
            str_contains($ua, 'Android') && ! str_contains($ua, 'Mobile') => 'tablet',
            $mobileHint === '?1',
            (bool) preg_match('/Mobi|iPhone|iPod|Android|Windows Phone/i', $ua) => 'mobile',
            default => 'desktop',
        };
    }

    private function device(Request $request): string
    {
        return self::deviceFor($request->userAgent(), $request->header('Sec-CH-UA-Mobile'));
    }

    // Als installierte App gestartet – erkennbar an der Startadresse /app aus dem Web-App-Manifest
    private function isStandalone(Request $request): bool
    {
        return $request->routeIs('app.pwa.dashboard');
    }

    private function externalReferrer(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        if (! $host || $host === $request->getHost()) {
            return null;
        }

        return mb_substr(preg_replace('/^www\./', '', strtolower($host)), 0, 100);
    }
}
