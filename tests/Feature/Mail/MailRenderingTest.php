<?php

namespace Tests\Feature\Mail;

use App\Mail\NewsletterMail;
use App\Mail\NewUserMail;
use App\Mail\SubscriptionCreatedMail;
use App\Mail\TicketAdminMail;
use App\Mail\TicketUserMail;
use App\Mail\UserAdminDeletedMail;
use App\Mail\UserDeletedMail;
use App\Mail\WelcomeMail;
use App\Models\State;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailRenderingTest extends TestCase
{
    use RefreshDatabase;

    private const XSS = '<img src=x onerror=alert(1)>';

    public function test_user_mails_render_with_branding(): void
    {
        $user = User::factory()->create(['name' => 'Max']);

        foreach ([
            [new WelcomeMail('Max'), 'Schön, dass du da bist, Max!', route('app.catched.create')],
            [new SubscriptionCreatedMail($user), 'Danke für dein Vertrauen, Max!', route('app.dashboard')],
            [new UserDeletedMail('Max'), 'Schade, dass du gehst, Max.', 'info@petrilog.com'],
        ] as [$mail, $headline, $expected]) {
            $html = $mail->render();

            $this->assertStringContainsString($headline, $html);
            $this->assertStringContainsString($expected, $html);
            $this->assertStringContainsString('logo-short.png', $html);
            $this->assertStringContainsString(route('public.imprint'), $html);
        }
    }

    public function test_ticket_mails_show_category_and_escape_user_input(): void
    {
        $user = User::factory()->create(['name' => self::XSS]);
        $ticket = Ticket::create([
            'title' => 'Upload ' . self::XSS,
            'category' => 'error',
            'url' => '/catched/create',
            'description' => '<ol><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Android</li></ol>',
            'user_id' => $user->id,
        ]);

        $admin = (new TicketAdminMail(['ticket' => $ticket, 'user' => $user]))->render();
        $confirmation = (new TicketUserMail(['ticket' => $ticket, 'user' => $user]))->render();

        foreach ([$admin, $confirmation] as $html) {
            $this->assertStringNotContainsString(self::XSS, $html);
            $this->assertStringContainsString('Fehlermeldung', $html);
        }

        // Quill-Bullet-Liste wird zur echten Aufzählung, UI-Reste verschwinden
        $this->assertStringContainsString('<ul', $admin);
        $this->assertStringNotContainsString('ql-ui', $admin);
        $this->assertStringContainsString('href="' . url('/catched/create') . '"', $admin);
        $this->assertStringContainsString(route('admin.tickets.show', $ticket), $admin);
    }

    public function test_admin_user_mails_escape_user_data(): void
    {
        $user = User::factory()->create([
            'name' => self::XSS,
            'state_id' => State::factory()->create(['name' => 'Kärnten'])->id,
        ]);

        $newUser = (new NewUserMail($user))->render();
        $deleted = (new UserAdminDeletedMail(['name' => self::XSS, 'email' => 'x@example.com']))->render();

        foreach ([$newUser, $deleted] as $html) {
            $this->assertStringNotContainsString(self::XSS, $html);
            $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        }
        $this->assertStringContainsString('Kärnten', $newUser);
    }

    public function test_newsletter_renders_content_and_unsubscribe_link(): void
    {
        $user = User::factory()->create();

        $html = (new NewsletterMail('Oktober-News', '<p>Hallo <strong>Petri</strong></p>', $user))->render();

        $this->assertStringContainsString('<strong>Petri</strong>', $html);
        $this->assertStringContainsString('Newsletter abbestellen', $html);
        $this->assertStringContainsString('/newsletter/unsubscribe/' . $user->id, $html);
    }

    public function test_auth_notifications_are_german(): void
    {
        $user = User::factory()->create();

        $verify = (new VerifyEmail)->toMail($user);
        $reset = (new ResetPassword('token'))->toMail($user);

        $this->assertSame('E-Mail-Adresse bestätigen', $verify->subject);
        $this->assertSame('Passwort zurücksetzen', $reset->subject);

        $html = (string) $reset->render();
        $this->assertStringContainsString('Neues Passwort festlegen', $html);
        $this->assertStringContainsString('Petri Heil', $html);
        $this->assertStringNotContainsString('Regards', $html);
    }
}
