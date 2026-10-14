<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriticalUserFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_can_calculate_but_cannot_open_member_or_admin_pages(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('tax.show'))->assertOk();

        $this->post(route('tax.calculate'), [
            'tax_year' => 2569,
            'married-status' => 'single',
            'income_g1' => 500000,
            'income_g5_type' => 'none',
            'income_g5_method' => 'flat',
            'income_g6_type' => 'none',
            'income_g8_type' => 'none',
            'income_g8_method' => 'actual',
            'self_exemption' => 'none',
            'exemption_income_type' => 1,
        ])->assertOk()->assertSee('ผลคำนวณภาษีทั้งปี');

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('tax.records.index'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->post(route('tax.save'), ['title' => 'Guest'])->assertRedirect(route('login'));
    }

    public function test_user_can_register_calculate_save_and_open_own_history(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Member Test',
            'email' => 'member@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $user = User::where('email', 'member@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->role);
        $this->assertAuthenticatedAs($user);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
        $this->get(route('admin.dashboard'))->assertForbidden();

        $this->post(route('tax.calculate'), [
            'tax_year' => 2569,
            'married-status' => 'single',
            'income_g1' => 500000,
            'income_g5_type' => 'none',
            'income_g5_method' => 'flat',
            'income_g6_type' => 'none',
            'income_g8_type' => 'none',
            'income_g8_method' => 'actual',
            'self_exemption' => 'none',
            'exemption_income_type' => 1,
        ])->assertOk();

        $this->post(route('tax.save'), [
            'title' => 'ผลคำนวณของสมาชิก',
        ])->assertRedirect();

        $record = $user->taxRecords()->first();
        $this->assertNotNull($record);

        $this->assertDatabaseHas('tax_records', [
            'user_id' => $user->id,
            'title' => 'ผลคำนวณของสมาชิก',
        ]);

        $this->get(route('tax.records.index'))
            ->assertOk()
            ->assertSee('ผลคำนวณของสมาชิก');

        $this->get(route('tax.records.show', $record))
            ->assertOk()
            ->assertSee('ผลคำนวณของสมาชิก');

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_can_open_member_and_admin_pages_and_change_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('profile.edit'))->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users'))->assertOk();
        $this->actingAs($admin)->get(route('admin.income-types'))->assertOk();
        $this->actingAs($admin)->get(route('admin.expense-types'))->assertOk();
        $this->actingAs($admin)->get(route('admin.deduction-types'))->assertOk();
        $this->actingAs($admin)->get(route('admin.tax-brackets'))->assertOk();

        $this->actingAs($admin)->patch(route('admin.users.role', $member), [
            'role' => 'admin',
        ])->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'role' => 'admin',
        ]);
    }
}
