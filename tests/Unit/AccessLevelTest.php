<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureMember;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

// ทดสอบสิทธิโดยไม่ migrate, seed หรือใช้ฐานข้อมูล
class AccessLevelTest extends TestCase
{
    private function userWithRole(string $role): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'Test';
        $user->email = 'test@example.com';
        $user->role = $role;
        return $user;
    }

    public function test_guests_are_redirected_from_member_and_admin_pages(): void
    {
        $this->get('/member')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/users')->assertRedirect(route('login'));
        $this->patch('/admin/users/2/role', ['role' => 'admin'])->assertRedirect(route('login'));
    }

    public function test_members_can_visit_member_page_but_not_admin_routes(): void
    {
        $this->actingAs($this->userWithRole('user'));
        $this->get('/member')->assertOk()->assertSee('User (สมาชิก)');
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->patch('/admin/users/2/role', ['role' => 'admin'])->assertForbidden();
    }

    public function test_admins_pass_both_permission_checks(): void
    {
        $request = Request::create('/admin/dashboard');
        $request->setUserResolver(fn () => $this->userWithRole('admin'));
        $next = fn () => response('allowed');
        $this->assertSame('allowed', (new EnsureAdmin())->handle($request, $next)->getContent());
        $this->assertSame('allowed', (new EnsureMember())->handle($request, $next)->getContent());
        $this->actingAs($this->userWithRole('admin'))->get('/member')->assertOk()->assertSee('Admin (ผู้ดูแลระบบ)');
    }

    public function test_unknown_roles_are_denied(): void
    {
        $this->actingAs($this->userWithRole('other'));
        $this->get('/member')->assertForbidden();
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $user = $this->userWithRole('admin');
        $request = Request::create('/admin/users/1/role', 'PATCH', ['role' => 'user']);
        $request->setUserResolver(fn () => $user);
        $response = (new AdminController())->updateRole($request, $user);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('admin', $user->role);
        $this->assertTrue(session('errors')->has('role'));
    }

    public function test_admin_can_change_another_users_role_without_mass_assignment(): void
    {
        $admin = $this->userWithRole('admin');
        $target = Mockery::mock(User::class)->makePartial();
        $target->id = 2;
        $target->role = 'user';
        $target->name = 'Original';
        $target->shouldReceive('save')->once()->andReturn(true);
        $request = Request::create('/admin/users/2/role', 'PATCH', ['role' => 'admin', 'name' => 'Injected']);
        $request->setUserResolver(fn () => $admin);
        $response = (new AdminController())->updateRole($request, $target);
        $this->assertSame('admin', $target->role);
        $this->assertSame('Original', $target->name);
        $this->assertSame(route('admin.users'), $response->getTargetUrl());
    }
}
