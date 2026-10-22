<?php

namespace Tests\Feature\Http;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MaintenancePageTest extends TestCase
{
    public function test_maintenance_response_uses_petrilog_page(): void
    {
        // Gleiche Exception wie PreventRequestsDuringMaintenance – ohne den echten Wartungsmodus
        // zu aktivieren (der würde über storage/framework/down auch die lokale App sperren)
        Route::get('/_test/down', fn () => abort(503));

        $this->get('/_test/down')
            ->assertStatus(503)
            ->assertSee('Wartungsarbeiten')
            ->assertSee('Wir sind gleich wieder', false)
            ->assertSee('logo-short.png')
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertDontSee('/build/assets', false); // eigenständig, ohne Vite-Bundle
    }
}
