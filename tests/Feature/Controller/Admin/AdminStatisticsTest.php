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
        $this->withHeaders(['User-Agent' => self::BROWSER, 'Referer' => 'https://www.google.com/search?q=petrilog'])
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
        $admin = User::factory()->create(['is_admin' => true]);

        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->get(route('public.pricing'));
        $this->withHeaders(['User-Agent' => self::BROWSER])->actingAs($admin)->get(route('admin.index'));
        $this->withHeaders(['User-Agent' => self::BROWSER, 'X-Inertia' => 'true', 'X-Inertia-Partial-Data' => 'fish'])
            ->actingAs($admin)->get(route('app.fish.index'));

        $this->assertSame(0, PageView::count());
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
        $admin = User::factory()->create(['is_admin' => true]);
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

    public function test_invalid_period_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.index', ['period' => 12]))
            ->assertSessionHasErrors('period');
    }
}
