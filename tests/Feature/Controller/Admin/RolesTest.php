<?php

namespace Tests\Feature\Controller\Admin;

use App\Models\Catched;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_editor_can_use_the_admin_area_but_not_development(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->get('/admin')->assertOk();
        $this->actingAs($editor)->get('/admin/user')->assertOk();
        $this->actingAs($editor)->get('/admin/fish')->assertOk();
        $this->actingAs($editor)->get('/admin/newsletter')->assertOk();

        $this->actingAs($editor)->get('/admin/development')->assertRedirect('/dashboard');
        $this->actingAs($editor)->get('/admin/development/wiki-export')->assertRedirect('/dashboard');
    }

    public function test_regular_users_cannot_enter_the_admin_area(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertRedirect('/dashboard');
    }

    public function test_editor_cannot_delete_users_or_change_roles(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $user = User::factory()->create();

        $this->actingAs($editor)->delete(route('admin.user.destroy', $user))->assertRedirect('/dashboard');
        $this->actingAs($editor)->patch(route('admin.user.role', $user), ['role' => 'admin'])->assertRedirect('/dashboard');

        $this->assertModelExists($user);
        $this->assertSame(User::ROLE_USER, $user->refresh()->role);
    }

    public function test_admin_can_change_roles_but_not_their_own(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.user.role', $user), ['role' => 'editor'])->assertSessionHas('success');
        $this->assertTrue($user->refresh()->isEditor());

        $this->actingAs($admin)->patch(route('admin.user.role', $user), ['role' => 'kaiser'])->assertSessionHasErrors('role');

        $this->actingAs($admin)->patch(route('admin.user.role', $admin), ['role' => 'user'])->assertSessionHas('error');
        $this->assertTrue($admin->refresh()->isAdmin());
    }

    public function test_admin_can_delete_a_user_with_all_catches(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        $catch = Catched::factory()->create(['user_id' => $user->id, 'fish_id' => \App\Models\Fish::factory()->create()->id]);

        $this->actingAs($admin)->delete(route('admin.user.destroy', $user))->assertSessionHas('success');

        $this->assertModelMissing($user);
        $this->assertModelMissing($catch);
    }

    public function test_admins_and_self_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $other = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->delete(route('admin.user.destroy', $admin))->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.user.destroy', $other))->assertSessionHas('error');

        $this->assertModelExists($admin);
        $this->assertModelExists($other);
    }

    public function test_impersonation_rules(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $user = User::factory()->create();

        $this->actingAs($editor);
        $this->assertTrue($editor->canImpersonate());
        $this->assertTrue($user->canBeImpersonated());
        $this->assertFalse($admin->canBeImpersonated());
        $this->assertFalse(User::factory()->create(['role' => User::ROLE_EDITOR])->canBeImpersonated());

        $this->actingAs($admin);
        $this->assertTrue($editor->canBeImpersonated());
        $this->assertFalse($user->canImpersonate());
    }

    public function test_impersonated_user_does_not_gain_admin_only_access_through_an_editor(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['impersonated_by' => $editor->id])
            ->get('/admin/development')->assertRedirect('/dashboard');

        $this->actingAs($user)->withSession(['impersonated_by' => $editor->id])
            ->get('/admin')->assertOk();
    }

    public function test_role_is_not_mass_assignable(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/user/profile-information', [
            'name' => $user->name, 'email' => $user->email, 'role' => 'admin',
        ]);

        $this->assertSame(User::ROLE_USER, $user->refresh()->role);
    }
}
