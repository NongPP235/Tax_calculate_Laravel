<?php

namespace Tests\Feature;

use App\Models\DeductionType;
use App\Models\ExpenseType;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\TaxRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxRecordCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function record(User $user): TaxRecord
    {
        $record = new TaxRecord();
        $record->user_id = $user->id;
        $record->tax_year = 2569;
        $record->title = 'รายการทดสอบ';
        $record->married_status = 'single';
        $record->total_income = 0;
        $record->total_expense = 0;
        $record->total_deduction = 0;
        $record->net_income = 0;
        $record->step_tax = 0;
        $record->tax_payable = 0;
        $record->save();
        return $record;
    }

    public function test_member_can_manage_items_and_caps_are_calculated_automatically(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $record = $this->record($user);
        $incomeType = IncomeType::where('code', '40_1')->first();

        $this->actingAs($user)->post(route('tax.incomes.store', $record), [
            'income_type_id' => $incomeType->id, 'amount' => 500000,
        ])->assertRedirect();
        $income = Income::where('tax_record_id', $record->id)->first();

        $expenseType = ExpenseType::where('tax_year', 2569)->where('code', 'salary_flat')->first();
        $this->actingAs($user)->post(route('tax.expenses.store', $record), [
            'income_id' => $income->id, 'expense_type_id' => $expenseType->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('expenses', ['income_id' => $income->id, 'allowed_amount' => 100000]);

        $deductionType = DeductionType::where('tax_year', 2569)->where('code', 'health_insurance')->first();
        $this->actingAs($user)->post(route('tax.deductions.store', $record), [
            'deduction_type_id' => $deductionType->id, 'amount' => 40000,
        ])->assertRedirect();
        $this->assertDatabaseHas('deductions', ['tax_record_id' => $record->id, 'allowed_amount' => 25000]);

        $this->actingAs($user)->put(route('tax.incomes.update', [$record, $income]), [
            'income_type_id' => $incomeType->id, 'amount' => 600000,
        ])->assertRedirect();
        $this->assertDatabaseHas('incomes', ['id' => $income->id, 'amount' => 600000]);

        $this->actingAs($user)->delete(route('tax.incomes.destroy', [$record, $income]))->assertRedirect();
        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }

    public function test_member_cannot_manage_another_users_record(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $record = $this->record($owner);
        $incomeType = IncomeType::where('code', '40_1')->first();

        $this->actingAs($other)->post(route('tax.incomes.store', $record), [
            'income_type_id' => $incomeType->id, 'amount' => 100000,
        ])->assertForbidden();
    }

    public function test_calculation_is_saved_as_separate_income_expense_and_deduction_items(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->post(route('tax.calculate'), [
            'tax_year' => 2569, 'married-status' => 'single',
            'income_g1' => 500000, 'income_g5_type' => 'none',
            'income_g5_method' => 'flat', 'income_g6_type' => 'none',
            'income_g8_type' => 'none', 'income_g8_method' => 'actual',
            'self_exemption' => 'none', 'exemption_income_type' => 1,
        ])->assertOk();

        $this->actingAs($user)->post(route('tax.save'), ['title' => 'ภาษีทดสอบ'])->assertRedirect();
        $record = TaxRecord::where('user_id', $user->id)->first();
        $this->assertNotNull($record);
        $this->assertDatabaseHas('incomes', ['tax_record_id' => $record->id, 'amount' => 500000]);
        $this->assertDatabaseHas('expenses', ['allowed_amount' => 100000]);
        $this->assertDatabaseHas('deductions', ['tax_record_id' => $record->id, 'allowed_amount' => 60000]);
    }
}
