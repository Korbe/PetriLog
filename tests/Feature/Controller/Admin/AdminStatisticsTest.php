<?php

namespace Tests\Feature\Controller\Admin;

use App\Mail\WelcomeMail;
use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\MailLog;
use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminStatisticsTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36';

    public function test_page_views_are_tracked_without_storing_the_ip(): void
    {
        $this->withHeaders(['User-Agent' => self::BROWSER, 'Accept-Language' => 'de-AT,de;q=0.9', 'Referer' => 'https://www.google.com/search?q=petrilog'])
            ->get(route('public.pricing'))
            ->assertOk();

        $view = PageView::sole();
        $this->assertSame('public.pricing', $view->route);
        $this->assertSame('google.com', $view->referrer_host);
        $this->assertFalse($view->authenticated);
        $this->assertSame(16, strlen($view->visitor));
        $this->assertStringNotContainsString('127.0.0.1', json_encode($view->toArray()));
    }

    public function test_bots_admin_pages_and_partial_reloads_are_not_counted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->get(route('public.pricing'));
        $this->withHeaders(['User-Agent' => self::BROWSER, 'Accept-Language' => 'de-AT,de;q=0.9'])->actingAs($admin)->get(route('admin.index'));
        $this->withHeaders(['User-Agent' => self::BROWSER, 'Accept-Language' => 'de-AT,de;q=0.9', 'X-Inertia' => 'true', 'X-Inertia-Partial-Data' => 'fish'])
            ->actingAs($admin)->get(route('app.fish.index'));

        $this->assertSame(0, PageView::count());
    }

    public function test_mail_link_scanners_and_link_previews_are_not_counted(): void
    {
        // Sicherheits-Scanner (z. B. Outlook Safe Links): Browser-Kennung, aber keine Sprach-Präferenz
        $this->withHeaders(['User-Agent' => self::BROWSER, 'Accept-Language' => ''])->get(route('public.pricing'))->assertOk();
        // Link-Vorschau (WhatsApp) und HTTP-Bibliothek
        $this->flushHeaders()->withHeaders(['User-Agent' => 'WhatsApp/2.23.20.0 A', 'Accept-Language' => 'de'])->get(route('public.pricing'));
        $this->flushHeaders()->withHeaders(['User-Agent' => 'axios/1.6.0', 'Accept-Language' => 'de'])->get(route('public.pricing'));

        $this->assertSame(0, PageView::count());

        // Echter Browser zählt
        $this->flushHeaders()->withHeaders(['User-Agent' => self::BROWSER, 'Accept-Language' => 'de-AT,de;q=0.9'])->get(route('public.pricing'));
        $this->assertSame(1, PageView::count());

        $this->artisan('stats:traffic')->expectsOutputToContain('1 Seitenaufrufe von 1 Besuchern')->assertSuccessful();
    }

    public function test_statistics_can_be_reset_with_confirmation(): void
    {
        PageView::create(['date' => now()->toDateString(), 'path' => '/', 'visitor' => 'aaaaaaaaaaaaaaaa']);
        MailLog::create(['type' => 'WelcomeMail', 'subject' => 'Hallo', 'recipient' => 'max@example.com']);

        // Abgelehnt → nichts passiert
        $this->artisan('stats:reset')->expectsConfirmation('Statistik wirklich zurücksetzen?', 'no')->assertSuccessful();
        $this->assertSame(1, PageView::count());

        // Standard: nur Zugriffsstatistik, Mail-Protokoll bleibt
        $this->artisan('stats:reset')->expectsConfirmation('Statistik wirklich zurücksetzen?', 'yes')->assertSuccessful();
        $this->assertSame(0, PageView::count());
        $this->assertSame(1, MailLog::count());

        $this->artisan('stats:reset --mails --force')->assertSuccessful();
        $this->assertSame(0, MailLog::count());
    }

    public function test_sent_mails_are_logged_with_type_and_recipient(): void
    {
        Mail::to('max@example.com')->send(new WelcomeMail('Max'));

        $log = MailLog::sole();
        $this->assertSame('WelcomeMail', $log->type);
        $this->assertSame('max@example.com', $log->recipient);
        $this->assertNotEmpty($log->subject);
        $this->assertSame('Willkommen', MailLog::label($log->type));
    }

    public function test_admin_dashboard_shows_statistics_for_the_period(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $angler = User::factory()->create();

        Catched::factory()->forUser($angler)->count(3)->create([
            'fish_id' => Fish::factory()->create(['name' => 'Hecht'])->id,
            'lake_id' => Lake::factory()->create()->id,
            'river_id' => null,
        ]);
        PageView::create(['date' => now('Europe/Vienna')->toDateString(), 'route' => 'public.index', 'path' => '/', 'visitor' => 'aaaaaaaaaaaaaaaa']);
        PageView::create(['date' => now('Europe/Vienna')->toDateString(), 'route' => 'public.index', 'path' => '/', 'visitor' => 'aaaaaaaaaaaaaaaa']);
        PageView::create(['date' => now('Europe/Vienna')->toDateString(), 'route' => 'public.pricing', 'path' => '/pricing', 'visitor' => 'bbbbbbbbbbbbbbbb']);
        MailLog::create(['type' => 'TicketUserMail', 'subject' => 'Ticket erhalten', 'recipient' => 'max@example.com']);

        $this->actingAs($admin)
            ->get(route('admin.index', ['period' => 7]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/Index')
                ->where('stats.period', 7)
                ->where('stats.kpis.views.value', 3)
                ->where('stats.kpis.visitors.value', 2)
                ->where('stats.kpis.catches.value', 3)
                ->where('stats.kpis.activeUsers.value', 1)
                ->where('stats.kpis.mails.value', 1)
                ->where('stats.mailsByType.0.label', 'Ticket-Bestätigung')
                ->where('stats.topPages.0.page', 'Startseite')
                ->where('stats.topFish.0.name', 'Hecht')
                ->has('stats.series.labels', 7)
                ->where('stats.series.catches', fn($days) => collect($days)->sum() === 3));
    }

    public function test_device_and_installed_app_are_tracked_and_summarised(): void
    {
        $iphone = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148';
        $windows = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36';

        // Start der installierten App über /app (start_url des Manifests)
        $this->actingAs(User::factory()->create())->withHeaders(['User-Agent' => $iphone, 'Accept-Language' => 'de-AT'])->get('/app');
        auth()->logout();
        $this->flushHeaders()->withHeaders(['User-Agent' => $windows, 'Accept-Language' => 'de-AT'])->get('/pricing');

        $this->assertDatabaseHas('page_views', ['device' => 'mobile', 'standalone' => true, 'route' => 'app.pwa.dashboard']);
        $this->assertDatabaseHas('page_views', ['device' => 'desktop', 'standalone' => false]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.index'))
            ->assertInertia(fn(Assert $page) => $page
                ->where('stats.devices.total', 2)
                ->where('stats.devices.appVisitors', 1)
                ->where('stats.devices.rows.0.key', 'mobile')
                ->where('stats.devices.rows.0.visitors', 1)
                ->where('stats.devices.rows.2.visitors', 1));
    }

    public function test_three_year_view_is_grouped_by_month_and_page_views_are_kept_three_years(): void
    {
        $today = now('Europe/Vienna');
        PageView::create(['date' => $today->copy()->subYears(2)->toDateString(), 'path' => '/', 'visitor' => 'aaaaaaaaaaaaaaaa']);
        PageView::create(['date' => $today->copy()->subYears(3)->subDays(2)->toDateString(), 'path' => '/', 'visitor' => 'bbbbbbbbbbbbbbbb']);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.index', ['period' => 1095]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->where('stats.series.grouping', 'month')
                ->where('stats.series.labels', fn($labels) => count($labels) >= 36 && count($labels) <= 37
                    && collect($labels)->last() === $today->copy()->startOfMonth()->toDateString())
                ->where('stats.kpis.views.value', 1));

        $this->artisan('model:prune', ['--model' => [PageView::class]]);
        $this->assertSame(1, PageView::count(), 'nur der Aufruf älter als 3 Jahre wird gelöscht');
    }

    public function test_scheduled_and_running_jobs_are_not_reported_as_a_queue_jam(): void
    {
        $job = fn(array $attributes) => \Illuminate\Support\Facades\DB::table('jobs')->insert([
            'queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'reserved_at' => null,
            'available_at' => time(), 'created_at' => time() - 3600, ...$attributes,
        ]);
        $job(['available_at' => time() + 3600]);                     // Wetter, erst in einer Stunde fällig
        $job(['reserved_at' => time() - 1800, 'attempts' => 1]);     // läuft gerade (z. B. Backup)

        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.index'))
            ->assertInertia(fn(Assert $page) => $page
                ->where('stats.health.queuedJobs', 0)
                ->where('stats.health.oldestJobMinutes', null)
                ->where('stats.health.scheduledJobs', 1));

        $job(['available_at' => time() - 1200]);                     // seit 20 Min. fällig, nicht begonnen
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.index'))
            ->assertInertia(fn(Assert $page) => $page
                ->where('stats.health.queuedJobs', 1)
                ->where('stats.health.oldestJobMinutes', 20));
    }

    public function test_invalid_period_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.index', ['period' => 12]))
            ->assertSessionHasErrors('period');
    }
}
