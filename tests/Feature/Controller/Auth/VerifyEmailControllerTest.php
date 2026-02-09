<?php

namespace Tests\Feature\Controller\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerifyEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_verify_email(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($url);

        $response->assertRedirect('/login');
    }

    public function test_email_cannot_be_verified_with_invalid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        // absichtlich KEIN signed link
        $response = $this->get(
            route('verification.verify', [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ])
        );

        $response->assertStatus(403);
    }

    public function test_user_can_verify_email_with_valid_signed_link(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($url);

        $response->assertRedirect(route('app.dashboard'));
        $response->assertSessionHas('success', 'Deine E-Mail wurde erfolgreich bestätigt!');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
