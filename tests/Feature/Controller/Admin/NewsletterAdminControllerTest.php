<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\User;
use Inertia\Testing\Assert;
use App\Mail\NewsletterMail;
use App\Jobs\SendNewsletterMail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsletterAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_newsletter_admin_routes(): void
    {
        $this->get(route('admin.newsletter.index'))->assertRedirect('/login');
        $this->post(route('admin.newsletter.send'), [])->assertRedirect('/login');
        $this->post(route('admin.newsletter.test'), [])->assertRedirect('/login');
    }

    public function test_user_cannot_access_newsletter_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.newsletter.index'))->assertRedirect('/dashboard');
        $this->actingAs($user)->post(route('admin.newsletter.send'), [])->assertRedirect('/dashboard');
        $this->actingAs($user)->post(route('admin.newsletter.test'), [])->assertRedirect('/dashboard');
    }

    public function test_admin_can_view_newsletter_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.newsletter.index'));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/Newsletter/Index')
        );
    }

    public function test_admin_can_send_newsletter_to_verified_users(): void
    {
        Mail::fake();
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true, 'newsletter_opt_out' => true,]);

        // Zwei User mit verified Email
        $users = User::factory()->count(2)->create([
            'email_verified_at' => now(),
            'newsletter_opt_out' => false,
        ]);

        // Ein User, der ausgeloggt/opted out ist
        User::factory()->create([
            'email_verified_at' => now(),
            'newsletter_opt_out' => true,
        ]);

        $data = [
            'subject' => 'Test Newsletter',
            'content' => 'Inhalt des Newsletters',
        ];

        $response = $this->actingAs($admin)->post(route('admin.newsletter.send'), $data);

        $response->assertRedirect(route('admin.newsletter.index'));
        $response->assertSessionHas('success', 'Newsletter an 2 User erfolgreich verschickt 🎉');

        // Job wurde für die 2 User dispatched
        Queue::assertPushed(SendNewsletterMail::class, 2);

        // Mail an System-Adresse gesendet
        Mail::assertSent(NewsletterMail::class, function ($mail) {
            return $mail->hasTo('info@petrilog.com');
        });
    }

    public function test_admin_can_send_test_newsletter_to_self(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['is_admin' => true]);

        $data = [
            'subject' => 'Test',
            'content' => 'Test Inhalt',
        ];

        $response = $this->actingAs($admin)->post(route('admin.newsletter.test'), $data);

        $response->assertRedirect(route('admin.newsletter.index'));
        $response->assertSessionHas('success', 'Test-Mail wurde an dich gesendet.');

        Queue::assertPushed(SendNewsletterMail::class, function ($job) use ($admin) {
            return $job->user->id === $admin->id
                && $job->subject === 'Test'
                && $job->content === 'Test Inhalt';
        });
    }

    public function test_user_can_unsubscribe_from_newsletter(): void
    {
        $user = User::factory()->create([
            'newsletter_opt_out' => false
        ]);

        $url = route('public.newsletter.unsubscribe', $user);

        // signed URL simulieren (da Middleware 'signed')
        $signedUrl = URL::signedRoute('public.newsletter.unsubscribe', ['user' => $user->id]);

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertStatus(200);
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Public/Newsletter/Unsubscribed')
        );

        $this->assertTrue((bool)$user->fresh()->newsletter_opt_out);
    }
}
