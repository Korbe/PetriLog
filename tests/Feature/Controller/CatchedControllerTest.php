<?php

namespace Tests\Feature\Controller;

use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatchedControllerTest extends TestCase
{
    use RefreshDatabase;

    private const MALICIOUS_REMARK = '<p>Schöner Hecht<script>alert(document.cookie)</script></p>'
        . '<p><img src="x" onerror="alert(1)"><strong onclick="alert(2)">fett</strong></p>'
        . '<p><a href="javascript:alert(3)">klick</a></p>';

    private function catchPayload(array $overrides = []): array
    {
        return array_merge([
            'date' => '2026-05-01',
            'fish_id' => Fish::factory()->create()->id,
            'lake_id' => Lake::factory()->create()->id,
            'length' => 55,
            'latitude' => 47.8,
            'longitude' => 13.0,
        ], $overrides);
    }

    private function assertRemarkIsClean(?string $remark): void
    {
        $this->assertNotNull($remark);
        $this->assertStringNotContainsStringIgnoringCase('<script', $remark);
        $this->assertStringNotContainsStringIgnoringCase('alert(', $remark);
        $this->assertStringNotContainsStringIgnoringCase('onerror', $remark);
        $this->assertStringNotContainsStringIgnoringCase('onclick', $remark);
        $this->assertStringNotContainsStringIgnoringCase('<img', $remark);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $remark);

        // Harmloser Inhalt bleibt erhalten
        $this->assertStringContainsString('<p>Schöner Hecht</p>', $remark);
        $this->assertStringContainsString('<strong>fett</strong>', $remark);
    }

    public function test_store_sanitizes_remark_html(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('app.catched.store'), $this->catchPayload([
                'remark' => self::MALICIOUS_REMARK,
            ]));

        $catched = Catched::where('user_id', $user->id)->sole();

        $response->assertRedirect(route('app.catched.show', $catched->id));
        $this->assertRemarkIsClean($catched->remark);
    }

    public function test_update_sanitizes_remark_html(): void
    {
        $user = User::factory()->create();
        $catched = Catched::factory()->forUser($user)->create([
            'fish_id' => Fish::factory()->create()->id,
            'lake_id' => Lake::factory()->create()->id,
            'river_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('app.catched.update', $catched->id), $this->catchPayload([
                'remark' => self::MALICIOUS_REMARK,
            ]));

        $response->assertRedirect(route('app.catched.show', $catched->id));
        $this->assertRemarkIsClean($catched->fresh()->remark);
    }

    public function test_store_keeps_quill_toolbar_markup(): void
    {
        $user = User::factory()->create();

        $remark = '<p><strong>B</strong> <em>I</em> <u>U</u> <s>S</s><br></p>'
            . '<ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>eins</li>'
            . '<li data-list="bullet">zwei</li></ol>'
            . '<p><a href="https://petrilog.com" rel="noopener noreferrer" target="_blank">Link</a> '
            . '<a href="mailto:info@petrilog.com">Mail</a></p>';

        $this->actingAs($user)
            ->post(route('app.catched.store'), $this->catchPayload(['remark' => $remark]));

        $stored = Catched::where('user_id', $user->id)->sole()->remark;

        $this->assertStringContainsString('<strong>B</strong> <em>I</em> <u>U</u> <s>S</s><br />', $stored);
        $this->assertStringContainsString('<ol><li data-list="ordered">eins</li><li data-list="bullet">zwei</li></ol>', $stored);
        $this->assertStringContainsString('<a href="https://petrilog.com" target="_blank" rel="noopener noreferrer">Link</a>', $stored);
        $this->assertStringContainsString('href="mailto:', $stored);
        $this->assertStringNotContainsString('ql-ui', $stored);
        $this->assertStringNotContainsString('contenteditable', $stored);
    }

    public function test_remark_that_is_only_a_script_is_stored_as_null(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('app.catched.store'), $this->catchPayload([
                'remark' => '<script>alert(1)</script>',
            ]));

        $this->assertNull(Catched::where('user_id', $user->id)->sole()->remark);
    }
}
