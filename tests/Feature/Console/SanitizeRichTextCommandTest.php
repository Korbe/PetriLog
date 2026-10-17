<?php

namespace Tests\Feature\Console;

use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SanitizeRichTextCommandTest extends TestCase
{
    use RefreshDatabase;

    private function catchWithRawRemark(string $remark): Catched
    {
        $user = User::factory()->create();
        $catched = Catched::factory()->forUser($user)->create([
            'fish_id' => Fish::factory()->create()->id,
            'lake_id' => Lake::factory()->create()->id,
            'river_id' => null,
        ]);

        // Am Mutator vorbei, wie Altdaten vor der Bereinigung
        DB::table('catcheds')->where('id', $catched->id)->update(['remark' => $remark]);

        return $catched;
    }

    public function test_command_sanitizes_existing_remarks(): void
    {
        $catched = $this->catchWithRawRemark('<p>Alt<img src=x onerror=alert(1)></p>');

        $this->artisan('richtext:sanitize')->assertSuccessful();

        $this->assertSame('<p>Alt</p>', DB::table('catcheds')->where('id', $catched->id)->value('remark'));
    }

    public function test_dry_run_does_not_change_rows(): void
    {
        $raw = '<p>Alt<script>alert(1)</script></p>';
        $catched = $this->catchWithRawRemark($raw);

        $this->artisan('richtext:sanitize', ['--dry-run' => true])
            ->expectsOutputToContain('catcheds: 1')
            ->assertSuccessful();

        $this->assertSame($raw, DB::table('catcheds')->where('id', $catched->id)->value('remark'));
    }
}
