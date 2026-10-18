<?php

namespace Tests\Feature\Controller\Admin;

use App\Models\Fish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EditorTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_edits_are_attributed_and_shown_in_the_admin_list(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR, 'name' => 'Erika Editor']);
        $fish = Fish::factory()->create();
        $this->assertNull($fish->updated_by);

        $this->actingAs($editor);
        $fish->update(['name' => 'Huchen']);
        $this->assertSame($editor->id, $fish->refresh()->updated_by);

        $this->get(route('admin.fish.index'))->assertInertia(fn(Assert $page) => $page
            ->where('fishs.0.updated_by', 'Erika Editor'));
    }

    public function test_regular_users_do_not_overwrite_the_editor(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);
        $fish = Fish::factory()->create();

        $this->actingAs(User::factory()->create());
        $fish->update(['name' => 'Anders']);

        $this->assertSame($admin->id, $fish->refresh()->updated_by);
    }
}
