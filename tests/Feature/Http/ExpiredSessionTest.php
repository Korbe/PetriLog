<?php

namespace Tests\Feature\Http;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExpiredSessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CSRF-Prüfung ist in Tests aus – die Route wirft den Fehler selbst, wie VerifyCsrfToken es täte
        Route::middleware('web')->post('/_test/expired', fn () => throw new TokenMismatchException('CSRF token mismatch.'));
    }

    public function test_expired_form_redirects_back_with_notice(): void
    {
        $this->from('/login')
            ->post('/_test/expired', ['email' => 'demo@petrilog.com'])
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Sitzung abgelaufen, bitte erneut versuchen.');
    }

    public function test_inertia_request_is_redirected_too(): void
    {
        $this->from('/catched/create')
            ->post('/_test/expired', [], ['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'text/html, application/xhtml+xml'])
            ->assertRedirect('/catched/create');
    }

    public function test_json_request_keeps_419(): void
    {
        $this->postJson('/_test/expired')->assertStatus(419);
    }
}
