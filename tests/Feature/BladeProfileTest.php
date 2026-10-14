<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BladeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_and_update_profile(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('User · สมาชิก');

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Member Name', 'email' => 'member@example.com',
            'password' => '', 'password_confirmation' => '',
        ])->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Member Name']);
    }

    public function test_admin_profile_shows_admin_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('profile.edit'))
            ->assertOk()->assertSee('Admin · ผู้ดูแลระบบ')->assertSee('จัดการระบบ');
    }
}
