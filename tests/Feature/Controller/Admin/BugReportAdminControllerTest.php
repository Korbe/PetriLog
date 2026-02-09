<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\BugReport;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BugReportAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_or_non_admin_cannot_access_bugreports(): void
    {
        $report = BugReport::factory()->create();

        // Guest
        $response = $this->get(route('admin.bugreports.index'));
        $response->assertRedirect('/login');

        // Normaler User
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get(route('admin.bugreports.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('app.dashboard'));
        $response->assertSessionHas('error', 'Du hast keinen Zugriff.'); 
    }

    public function test_admin_can_view_bugreports_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $report = BugReport::factory()->create([
            'title' => 'Fehler A',
            'category' => 'UI',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.bugreports.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Admin/BugReports/Index')
                ->has(
                    'reports',
                    1,
                    fn($page) => $page
                        ->where('id', $report->id)
                        ->where('title', 'Fehler A')
                        ->where('category', 'UI')
                        ->where('user', $report->user ? $report->user->name : 'Unbekannt')
                        ->etc()
                )
        );
    }

    public function test_admin_can_view_single_bugreport(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // BugReport mit User
        $user = User::factory()->create(['name' => 'Max Mustermann']);

        $report = BugReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'Fehler B',
            'description' => 'Beschreibung B',
            'category' => 'Backend',
            'steps' => 'Schritt 1, 2',
            'url' => 'https://example.test',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.bugreports.show', $report->id));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Admin/BugReports/Show')
                ->where('report.id', $report->id)
                ->where('report.title', 'Fehler B')
                ->where('report.description', 'Beschreibung B')
                ->where('report.category', 'Backend')
                ->where('report.steps', 'Schritt 1, 2')
                ->where('report.url', 'https://example.test')
                ->where('report.user', 'Max Mustermann') // <--- hier den User prüfen
                ->etc()
        );
    }


    public function test_admin_can_delete_bugreport(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $report = BugReport::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.bugreports.destroy', $report->id));

        $response->assertRedirect(route('admin.bugreports.index'));
        $response->assertSessionHas('success', 'Bug-Report gelöscht.');

        $this->assertDatabaseMissing('bug_reports', ['id' => $report->id]);
    }
}
