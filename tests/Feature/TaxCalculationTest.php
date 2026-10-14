<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function form(array $changes = []): array
    {
        return array_replace([
            'tax_year' => 2569,
            'married-status' => 'single',
            'income_g5_type' => 'none',
            'income_g5_method' => 'flat',
            'income_g6_type' => 'none',
            'income_g8_type' => 'none',
            'income_g8_method' => 'actual',
            'self_exemption' => 'none',
            'exemption_income_type' => 1,
        ], $changes);
    }

    public function test_salary_and_paid_tax_are_calculated_and_inputs_are_preserved(): void
    {
        $this->get('/TaxCalculate')->assertOk();
        $this->post('/TaxCalculate', $this->form([
            'income_g1' => 500000, 'withholding_tax' => 12000, 'half_year_tax' => 1000,
        ]))->assertOk()->assertViewHas('netIncome', 340000)
            ->assertViewHas('taxPayable', 11500)->assertViewHas('refund', 1500)
            ->assertViewHas('withholdingTax', 12000.0)->assertViewHas('halfYearTax', 1000.0)
            ->assertViewHas('additionalTax', 0)->assertSee('value="500000"', false)
            ->assertSee('ภาษีหัก ณ ที่จ่าย')->assertSee('12,000.00')
            ->assertSee('ยอดขอคืนโดยประมาณ');
    }

    public function test_rental_rates_and_actual_expenses(): void
    {
        foreach (['building' => 150000, 'agriculture_land' => 100000,
            'other_land' => 75000, 'vehicle' => 150000, 'other_property' => 50000] as $type => $expense) {
            $this->post('/TaxCalculate', $this->form([
                'income_g5' => 500000, 'income_g5_type' => $type,
            ]))->assertOk()->assertViewHas('expense', $expense);
        }
        $this->post('/TaxCalculate', $this->form([
            'income_g5' => 500000, 'income_g5_type' => 'building',
            'income_g5_method' => 'actual', 'expense_g5' => 200000,
        ]))->assertOk()->assertViewHas('netIncome', 240000);
    }

    public function test_business_expenses_and_minimum_tax(): void
    {
        $this->post('/TaxCalculate', $this->form([
            'income_g8' => 1100000, 'income_g8_type' => 'other',
            'income_g8_method' => 'actual', 'expense_g8' => 1000000,
        ]))->assertOk()->assertViewHas('netIncome', 40000)
            ->assertViewHas('taxPayable', 5500);
        $this->post('/TaxCalculate', $this->form([
            'income_g8' => 1000000, 'income_g8_type' => 'other', 'expense_g8' => 900000,
        ]))->assertOk()->assertViewHas('taxPayable', 0);
        $this->post('/TaxCalculate', $this->form([
            'income_g8' => 500000, 'income_g8_type' => 'resale',
            'income_g8_method' => 'flat', 'business_confirmed' => 1,
        ]))->assertOk()->assertViewHas('expense', 300000);
    }

    public function test_children_spouse_and_care_deductions_accumulate(): void
    {
        $this->post('/TaxCalculate', $this->form([
            'income_g1' => 500000, 'married-status' => 'married_no_income',
            'child_count' => 2, 'child_bonus_count' => 1, 'children_confirmed' => 1,
            'disabled_family_count' => 1, 'care_confirmed' => 1,
        ]))->assertOk()->assertViewHas('deduction', 270000)
            ->assertViewHas('netIncome', 130000)->assertViewHas('taxPayable', 0);
    }

    public function test_disability_exemption_is_applied_before_expenses(): void
    {
        $this->post('/TaxCalculate', $this->form([
            'income_g1' => 250000, 'self_exemption' => 'disabled', 'taxpayer_age' => 30,
            'resident_confirmed' => 1, 'disability_confirmed' => 1,
        ]))->assertOk()->assertViewHas('exemptIncome', 190000)
            ->assertViewHas('expense', 30000)->assertViewHas('netIncome', 0);
        $this->post('/TaxCalculate', $this->form([
            'income_g1' => 500000, 'self_exemption' => 'elderly', 'taxpayer_age' => 65,
            'resident_confirmed' => 1,
        ]))->assertOk()->assertViewHas('exemptIncome', 190000);
    }

    public function test_insurance_donation_and_year_specific_social_limits(): void
    {
        $this->post('/TaxCalculate', $this->form([
            'income_g1' => 500000, 'deduct_life' => 100000, 'deduct_health' => 25000,
            'deduct_social' => 10500, 'deduct_donation' => 100000,
        ]))->assertOk()->assertViewHas('deduction', 170500)
            ->assertViewHas('donation', 22950)->assertViewHas('netIncome', 206550);
        $this->from('/TaxCalculate')->post('/TaxCalculate', $this->form([
            'tax_year' => 2568, 'deduct_social' => 10500,
        ]))->assertRedirect('/TaxCalculate')->assertSessionHasErrors('deduct_social');
    }

    public function test_invalid_claims_are_rejected_with_old_input_and_visible_errors(): void
    {
        $cases = [
            [['income_g1' => -1], 'income_g1'],
            [['tax_year' => 2570], 'tax_year'],
            [['income_g6' => 10000], 'income_g6_type'],
            [['income_g8' => 500000, 'income_g8_type' => 'other', 'income_g8_method' => 'flat'], 'income_g8_method'],
            [['child_count' => 1, 'child_bonus_count' => 2], 'child_bonus_count'],
            [['child_count' => 3, 'adopted_child_count' => 1], 'adopted_child_count'],
            [['disabled_other_count' => 2], 'disabled_other_count'],
            [['self_exemption' => 'elderly', 'taxpayer_age' => 64, 'resident_confirmed' => 1], 'taxpayer_age'],
            [['self_exemption' => 'disabled', 'taxpayer_age' => 66, 'resident_confirmed' => 1, 'disability_confirmed' => 1], 'disability_confirmed'],
        ];
        foreach ($cases as [$input, $error]) {
            $this->from('/TaxCalculate')->post('/TaxCalculate', $this->form($input))
                ->assertRedirect('/TaxCalculate')->assertSessionHasErrors($error);
        }
        $this->from('/TaxCalculate')->post('/TaxCalculate', $this->form(['income_g1' => -1]))
            ->assertSessionHasInput('income_g1', -1);
    }

    public function test_validation_redirect_displays_errors_and_preserves_input(): void
    {
        $this->followingRedirects()->from('/TaxCalculate')
            ->post('/TaxCalculate', $this->form(['income_g1' => -1]))
            ->assertOk()->assertSee('ยังคำนวณไม่ได้')->assertSee('value="-1"', false);
    }

    public function test_blank_amounts_and_profession_rates(): void
    {
        $this->post('/TaxCalculate', $this->form(['income_g1' => '', 'child_count' => '']))
            ->assertOk()->assertViewHas('taxPayable', 0);
        foreach (['medical' => 300000, 'other' => 150000] as $type => $expense) {
            $this->post('/TaxCalculate', $this->form([
                'income_g6_type' => $type, 'income_g6' => 500000,
            ]))->assertOk()->assertViewHas('expense', $expense);
        }
    }

    public function test_all_tax_brackets_and_fractional_income(): void
    {
        $controller = new \App\Http\Controllers\TaxCalculationController();
        foreach ([150000 => 0, 300000 => 7500, 500000 => 27500, 750000 => 65000,
            1000000 => 115000, 2000000 => 365000, 5000000 => 1265000] as $income => $tax) {
            $this->assertEquals($tax, $controller->calculateStepTax($income));
        }
        $this->assertEqualsWithDelta(7500.05, $controller->calculateStepTax(300000.5), 0.00001);
        $this->assertEqualsWithDelta(1265000.35, $controller->calculateStepTax(5000001), 0.00001);
    }
}
