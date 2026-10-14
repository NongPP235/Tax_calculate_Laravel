<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use App\Models\Deduction;
use App\Models\ExpenseType;
use App\Models\Expense;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\TaxBracket;
use App\Models\TaxRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaxCalculationController extends Controller
{
    public function show(){
        $form = [];
        $incomeTypes = IncomeType::where('is_active', true)->orderBy('code')->get()->keyBy('code');
        return view('calculate', compact('form', 'incomeTypes'));
    }

    // อ่านขั้นภาษีจากฐานข้อมูล เพื่อให้ Admin เปลี่ยนอัตราได้โดยไม่แก้ Controller
    public function calculateStepTax(float $netIncome, int $taxYear = 2569){
        $tax = 0;
        $brackets = TaxBracket::where('tax_year', $taxYear)
            ->orderBy('lower_bound')->get();

        foreach ($brackets as $bracket) {
            $lower = (float) $bracket->lower_bound;
            $upper = $bracket->upper_bound === null ? $netIncome : (float) $bracket->upper_bound;
            if ($netIncome > $lower) {
                $range = min($netIncome, $upper) - $lower;
                $tax += $range * (float) $bracket->rate;
            }
        }
        return $tax;
    }

    public function calculateTax(Request $request)
    {
        // 1. ช่องตัวเลขที่ไม่ได้กรอก ให้เป็น 0 ก่อนตรวจข้อมูล
        $numericFields = [
            'child_count', 'child_bonus_count', 'adopted_child_count',
            'disabled_family_count', 'disabled_other_count', 'taxpayer_age',
            'income_g1', 'income_g2', 'income_g3', 'income_g4',
            'income_g5', 'income_g6', 'income_g7', 'income_g8',
            'expense_g5', 'expense_g8', 'deduct_social', 'deduct_life',
            'deduct_health', 'deduct_donation', 'withholding_tax', 'half_year_tax',
        ];
        foreach ($numericFields as $field) {
            if ($request->input($field) === null || $request->input($field) === '') {
                $request->merge([$field => 0]);
            }
        }

        // 2. ตรวจสอบข้อมูลจากฟอร์ม
        $request->validate([
            'income_g1' => 'numeric|min:0|max:1000000000000',
            'income_g2' => 'numeric|min:0|max:1000000000000',
            'income_g3' => 'numeric|min:0|max:1000000000000',
            'income_g4' => 'numeric|min:0|max:1000000000000',
            'income_g5' => 'numeric|min:0|max:1000000000000',
            'income_g6' => 'numeric|min:0|max:1000000000000',
            'income_g7' => 'numeric|min:0|max:1000000000000',
            'income_g8' => 'numeric|min:0|max:1000000000000',
            'expense_g5' => 'numeric|min:0|max:1000000000000',
            'expense_g8' => 'numeric|min:0|max:1000000000000',
            'deduct_social' => 'numeric|min:0|max:1000000000000',
            'deduct_life' => 'numeric|min:0|max:1000000000000',
            'deduct_health' => 'numeric|min:0|max:1000000000000',
            'deduct_donation' => 'numeric|min:0|max:1000000000000',
            'withholding_tax' => 'numeric|min:0|max:1000000000000',
            'half_year_tax' => 'numeric|min:0|max:1000000000000',
            'child_count' => 'integer|min:0|max:100',
            'child_bonus_count' => 'integer|min:0|max:100',
            'adopted_child_count' => 'integer|min:0|max:3',
            'disabled_family_count' => 'integer|min:0|max:100',
            'disabled_other_count' => 'integer|min:0|max:1',
            'taxpayer_age' => 'integer|min:0|max:120',
            'tax_year' => 'required|integer|in:2568,2569',
            'married-status' => 'required|in:single,married_no_income,married_has_income',
            'income_g6_type' => 'nullable|in:none,medical,other',
            'income_g5_type' => 'nullable|in:none,building,agriculture_land,other_land,vehicle,other_property',
            'income_g5_method' => 'nullable|in:flat,actual',
            'income_g8_type' => 'nullable|in:none,resale,restaurant,other',
            'income_g8_method' => 'nullable|in:flat,actual',
            'self_exemption' => 'required|in:none,disabled,elderly',
            'exemption_income_type' => 'required|integer|between:1,8',
            'resident_confirmed' => 'nullable|boolean',
            'disability_confirmed' => 'nullable|boolean',
            'children_confirmed' => 'nullable|boolean',
            'care_confirmed' => 'nullable|boolean',
            'business_confirmed' => 'nullable|boolean',
        ], 
        [
            'required' => 'กรุณาระบุ :attribute',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
            'min' => ':attribute ต้องไม่น้อยกว่า :min',
            'max' => ':attribute ต้องไม่เกิน :max',
            'in' => 'กรุณาเลือก :attribute จากตัวเลือกที่รองรับ',
            'boolean' => 'ค่า :attribute ไม่ถูกต้อง',
        ],
        [
            'tax_year' => 'ปีภาษี', 'married-status' => 'สถานภาพสมรส',
            'income_g1' => 'รายได้ ม.40(1)',
            'income_g2' => 'รายได้ ม.40(2)',
            'income_g3' => 'รายได้ ม.40(3)',
            'income_g4' => 'รายได้ ม.40(4)',
            'income_g5' => 'รายได้ ม.40(5)',
            'income_g6' => 'รายได้ ม.40(6)',
            'income_g7' => 'รายได้ ม.40(7)',
            'income_g8' => 'รายได้ ม.40(8)',
            'child_count' => 'จำนวนบุตรชอบด้วยกฎหมายที่มีสิทธิ',
            'child_bonus_count' => 'จำนวนบุตรที่มีสิทธิเพิ่ม',
            'adopted_child_count' => 'จำนวนบุตรบุญธรรมที่มีสิทธิ',
            'income_g5_type' => 'ประเภททรัพย์สินให้เช่า',
            'income_g6_type' => 'ประเภทวิชาชีพ', 'income_g8_type' => 'ประเภทกิจการ',
            'expense_g5' => 'ค่าใช้จ่ายจริงค่าเช่า', 'expense_g8' => 'ค่าใช้จ่ายจริงธุรกิจ',
            'deduct_social' => 'ประกันสังคม', 'taxpayer_age' => 'อายุผู้มีเงินได้',
            'deduct_life' => 'เบี้ยประกันชีวิต',
            'deduct_health' => 'เบี้ยประกันสุขภาพ',
            'deduct_donation' => 'เงินบริจาค',
            'disabled_family_count' => 'จำนวนคนพิการในครอบครัวที่อุปการะ',
            'disabled_other_count' => 'จำนวนคนพิการอื่นที่อุปการะ',
            'withholding_tax' => 'ภาษีหัก ณ ที่จ่าย', 'half_year_tax' => 'ภาษีครึ่งปี',
        ]);

        $taxYear = (int) $request->input('tax_year');
        $incomeTypes = IncomeType::where('is_active', true)->orderBy('code')->get()->keyBy('code');
        $errors = [];
        for ($group = 1; $group <= 8; $group++) {
            if ((float) $request->input('income_g'.$group) > 0 && !$incomeTypes->has('40_'.$group)) {
                $errors['income_g'.$group] = 'ประเภทเงินได้ ม.40('.$group.') ถูกปิดใช้งานโดยผู้ดูแลระบบ';
            }
        }
        foreach ([5, 6, 8] as $group) {
            if ($request->input('income_g'.$group) > 0 && in_array($request->input('income_g'.$group.'_type'), [null, 'none'], true)) {
                $errors['income_g'.$group.'_type'] = 'กรุณาเลือกประเภทเมื่อมีรายได้ในส่วนนี้';
            }
        }
        if ($request->input('income_g8') > 0 && $request->input('income_g8_method') === 'flat') {
            if ($request->input('income_g8_type') === 'other') {
                $errors['income_g8_method'] = 'กิจการอื่นต้องเลือกค่าใช้จ่ายจริงในเครื่องมือนี้';
            } elseif (! $request->boolean('business_confirmed')) {
                $errors['business_confirmed'] = 'กรุณายืนยันว่ารายได้ทั้งหมดตรงกับกิจการที่เลือกก่อนหักเหมา';
            }
        }
        foreach ([5, 8] as $group) {
            if ($request->input('income_g'.$group.'_method') === 'actual'
                && $request->input('expense_g'.$group) > $request->input('income_g'.$group)) {
                $errors['expense_g'.$group] = 'ค่าใช้จ่ายจริงต้องไม่เกินรายได้ในส่วนนี้';
            }
        }
        $childCount = (int) $request->input('child_count');
        $childBonusCount = (int) $request->input('child_bonus_count');
        $adoptedCount = (int) $request->input('adopted_child_count');
        if ($childBonusCount > $childCount) {
            $errors['child_bonus_count'] = 'จำนวนบุตรที่มีสิทธิเพิ่มต้องไม่เกินบุตรชอบด้วยกฎหมายที่มีสิทธิ';
        }
        if ($adoptedCount > 0 && $childCount + $adoptedCount > 3) {
            $errors['adopted_child_count'] = 'เมื่อใช้สิทธิบุตรบุญธรรม จำนวนรวมที่ใช้สิทธิต้องไม่เกิน 3 คน';
        }
        if ($childCount + $adoptedCount > 0 && ! $request->boolean('children_confirmed')) {
            $errors['children_confirmed'] = 'กรุณายืนยันเงื่อนไขสิทธิลดหย่อนบุตร';
        }
        $careCount = (int) $request->input('disabled_family_count') + (int) $request->input('disabled_other_count');
        if ($careCount > 0 && ! $request->boolean('care_confirmed')) {
            $errors['care_confirmed'] = 'กรุณายืนยันเงื่อนไขและหลักฐานการอุปการะคนพิการ/ทุพพลภาพ';
        }
        $selfExemption = $request->input('self_exemption');
        if ($selfExemption !== 'none') {
            if (! $request->boolean('resident_confirmed')) {
                $errors['resident_confirmed'] = 'สิทธินี้ต้องเป็นผู้อยู่ในประเทศไทยตามเกณฑ์ภาษี';
            }
            $age = (int) $request->input('taxpayer_age');
            if ($selfExemption === 'disabled' && ($age > 65 || ! $request->boolean('disability_confirmed'))) {
                $errors['disability_confirmed'] = 'สิทธิคนพิการต้องมีหลักฐานตามเกณฑ์และอายุไม่เกิน 65 ปี';
            }
            if ($selfExemption === 'elderly' && $age < 65) {
                $errors['taxpayer_age'] = 'สิทธิผู้สูงอายุต้องมีอายุอย่างน้อย 65 ปีในปีภาษี';
            }
        }
        $socialSecurity = DeductionType::where('tax_year', $taxYear)->where('code', 'social_security')->where('is_active', true)->first();
        $socialCap = $socialSecurity === null ? 0 : (float) $socialSecurity->max_amount;
        if ($socialSecurity === null) {
            $errors['tax_year'] = 'ไม่พบข้อมูลค่าลดหย่อนของปีภาษีนี้ กรุณาให้ Admin เพิ่มข้อมูลก่อน';
        }
        if (TaxBracket::where('tax_year', $taxYear)->count() === 0) {
            $errors['tax_year'] = 'ไม่พบข้อมูลขั้นภาษีของปีภาษีนี้ กรุณาให้ Admin เพิ่มข้อมูลก่อน';
        }
        if ($request->input('deduct_social') > $socialCap) {
            $errors['deduct_social'] = 'ประกันสังคมปีภาษีนี้ต้องไม่เกิน '.number_format($socialCap).' บาท';
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        // 3. อ่านรายได้แต่ละประเภท
        $inc_g1 = (float) $request->input('income_g1');
        $inc_g2 = (float) $request->input('income_g2');
        $inc_g3 = (float) $request->input('income_g3');
        $inc_g4 = (float) $request->input('income_g4');
        $inc_g5 = (float) $request->input('income_g5');
        $inc_g6 = (float) $request->input('income_g6');
        $inc_g7 = (float) $request->input('income_g7');
        $inc_g8 = (float) $request->input('income_g8');
        $totalIncomeBeforeExempt = $inc_g1 + $inc_g2 + $inc_g3 + $inc_g4
            + $inc_g5 + $inc_g6 + $inc_g7 + $inc_g8;

        // 4. ยกเว้นเงินได้คนพิการหรือผู้สูงอายุ ก่อนหักค่าใช้จ่าย
        // เลือกใช้ได้หนึ่งสิทธิ และหนึ่งประเภทเงินได้ในฟอร์มนี้
        $exemptIncome = 0;
        if ($selfExemption !== 'none') {
            $incomeType = (int) $request->input('exemption_income_type');
            if ($incomeType === 1) {
                $exemptIncome = min(190000, $inc_g1);
                $inc_g1 -= $exemptIncome;
            }
            elseif ($incomeType === 2) {
                $exemptIncome = min(190000, $inc_g2);
                $inc_g2 -= $exemptIncome;
            }
            elseif ($incomeType === 3) {
                $exemptIncome = min(190000, $inc_g3);
                $inc_g3 -= $exemptIncome;
            }
            elseif ($incomeType === 4) {
                $exemptIncome = min(190000, $inc_g4);
                $inc_g4 -= $exemptIncome;
            }
            elseif ($incomeType === 5) {
                $exemptIncome = min(190000, $inc_g5);
                $inc_g5 -= $exemptIncome;
            }
            elseif ($incomeType === 6) {
                $exemptIncome = min(190000, $inc_g6);
                $inc_g6 -= $exemptIncome;
            }
            elseif ($incomeType === 7) {
                $exemptIncome = min(190000, $inc_g7);
                $inc_g7 -= $exemptIncome;
            }
            elseif ($incomeType === 8) {
                $exemptIncome = min(190000, $inc_g8);
                $inc_g8 -= $exemptIncome;
            }
        }

        $allIncome = $inc_g1 + $inc_g2 + $inc_g3 + $inc_g4
            + $inc_g5 + $inc_g6 + $inc_g7 + $inc_g8;

        // 5. หักค่าใช้จ่าย โดยอ่านอัตราและเพดานจากฐานข้อมูล
        $expense = 0;
        $expenseSalary = ExpenseType::where('tax_year', $taxYear)->where('code', 'salary_flat')->where('is_active', true)->first();
        $salaryRate = $expenseSalary === null ? 0 : (float) $expenseSalary->rate;
        $salaryCap = $expenseSalary === null ? 0 : (float) $expenseSalary->max_amount;
        $expense12 = min(($inc_g1 + $inc_g2) * $salaryRate, $salaryCap);
        $expense += $expense12;

        // ม.40(3) เฉพาะค่าแห่งกู๊ดวิลล์ ลิขสิทธิ์ และสิทธิอื่น
        $expenseRights = ExpenseType::where('tax_year', $taxYear)->where('code', 'rights_flat')->where('is_active', true)->first();
        $rightsRate = $expenseRights === null ? 0 : (float) $expenseRights->rate;
        $rightsCap = $expenseRights === null ? 0 : (float) $expenseRights->max_amount;
        $expense3 = min($inc_g3 * $rightsRate, $rightsCap);
        $expense += $expense3;

        // ม.40(4) หักค่าใช้จ่ายไม่ได้

        // ม.40(5) ค่าเช่า: หักตามจริง หรือเหมาตามประเภททรัพย์สิน
        if ($request->input('income_g5_method') === 'actual') {
            $expense5 = min((float) $request->input('expense_g5'), $inc_g5);
        } else {
            $rentalCodes = ['building' => 'building_flat', 'agriculture_land' => 'agriculture_land_flat',
                'other_land' => 'other_land_flat', 'vehicle' => 'vehicle_flat', 'other_property' => 'other_property_flat'];
            $rentalExpense = ExpenseType::where('tax_year', $taxYear)->where('code', $rentalCodes[$request->input('income_g5_type')] ?? '')->where('is_active', true)->first();
            $expense5 = $inc_g5 * ($rentalExpense === null ? 0 : (float) $rentalExpense->rate);
        }
        $expense += $expense5;

        // ม.40(6) แพทย์ 60% วิชาชีพอื่นที่ระบุ 30%
        $professionCode = $request->input('income_g6_type') === 'medical' ? 'medical_flat' : 'profession_other_flat';
        $professionExpense = ExpenseType::where('tax_year', $taxYear)->where('code', $professionCode)->where('is_active', true)->first();
        $expense += $inc_g6 * ($professionExpense === null ? 0 : (float) $professionExpense->rate);

        // ม.40(7) รับเหมาที่จัดหาสัมภาระสำคัญ หักเหมา 60%
        $constructionExpense = ExpenseType::where('tax_year', $taxYear)->where('code', 'construction_flat')->where('is_active', true)->first();
        $expense += $inc_g7 * ($constructionExpense === null ? 0 : (float) $constructionExpense->rate);

        // ม.40(8) รองรับหักเหมาเฉพาะซื้อสินค้ามาขายและร้านอาหาร
        $businessType = $request->input('income_g8_type');
        if ($request->input('income_g8_method') === 'actual') {
            $expense8 = min((float) $request->input('expense_g8'), $inc_g8);
        } else {
            $expense8 = 0;
            if ($businessType === 'resale' || $businessType === 'restaurant') {
                $businessCode = $businessType === 'resale' ? 'resale_flat' : 'restaurant_flat';
                $businessExpense = ExpenseType::where('tax_year', $taxYear)->where('code', $businessCode)->where('is_active', true)->first();
                $expense8 = $inc_g8 * ($businessExpense === null ? 0 : (float) $businessExpense->rate);
            }
        }
        $expense += $expense8;

        // 6. รวมค่าลดหย่อน โดยใช้ += เพื่อไม่ให้ยอดก่อนหน้าหาย
        $personalType = DeductionType::where('tax_year', $taxYear)->where('code', 'personal')->where('is_active', true)->first();
        $spouseType = DeductionType::where('tax_year', $taxYear)->where('code', 'spouse')->where('is_active', true)->first();
        $childType = DeductionType::where('tax_year', $taxYear)->where('code', 'child')->where('is_active', true)->first();
        $childBonusType = DeductionType::where('tax_year', $taxYear)->where('code', 'child_bonus')->where('is_active', true)->first();
        $adoptedChildType = DeductionType::where('tax_year', $taxYear)->where('code', 'adopted_child')->where('is_active', true)->first();
        $careType = DeductionType::where('tax_year', $taxYear)->where('code', 'disabled_care')->where('is_active', true)->first();
        $lifeType = DeductionType::where('tax_year', $taxYear)->where('code', 'life_insurance')->where('is_active', true)->first();
        $healthType = DeductionType::where('tax_year', $taxYear)->where('code', 'health_insurance')->where('is_active', true)->first();
        $deduction = $personalType === null ? 0 : (float) $personalType->max_amount;
        if ($request->input('married-status') === 'married_no_income') {
            $deduction += $spouseType === null ? 0 : (float) $spouseType->max_amount;
        }
        $deduction += ($childCount + $adoptedCount) * ($childType === null ? 0 : (float) $childType->max_amount);
        $deduction += $childBonusCount * ($childBonusType === null ? 0 : (float) $childBonusType->max_amount);
        $deduction += $careCount * ($careType === null ? 0 : (float) $careType->max_amount);

        $life = (float) $request->input('deduct_life');
        $health = (float) $request->input('deduct_health');
        $lifeCap = $lifeType === null ? 0 : (float) $lifeType->max_amount;
        $healthCap = $healthType === null ? 0 : (float) $healthType->max_amount;
        if ($life > $lifeCap) {
            $life = $lifeCap;
        }
        if ($health > $healthCap) {
            $health = $healthCap;
        }
        if ($life + $health > $lifeCap) {
            $health = $lifeCap - $life;
        }
        $deduction += (float) $request->input('deduct_social') + $life + $health;

        // 7. เงินบริจาคทั่วไป หักไม่เกิน 10% ของยอดหลังลดหย่อน
        $beforeDonation = $allIncome - $expense - $deduction;
        if ($beforeDonation < 0) {
            $beforeDonation = 0;
        }
        $donation = (float) $request->input('deduct_donation');
        $donationCap = $beforeDonation * 0.10;
        if ($donation > $donationCap) {
            $donation = $donationCap;
        }
        $netIncome = $beforeDonation - $donation;

        // 8. เปรียบเทียบภาษีขั้นบันไดกับวิธี 0.5%
        $stepTax = $this->calculateStepTax($netIncome, $taxYear);
        $minimumTax = 0;
        $nonSalaryIncome = $inc_g2 + $inc_g3 + $inc_g4 + $inc_g5
            + $inc_g6 + $inc_g7 + $inc_g8;
        if ($nonSalaryIncome >= 120000) {
            $minimumTax = $nonSalaryIncome * 0.005;
        }
        $taxPayable = $stepTax;
        if ($minimumTax > 5000 && $minimumTax > $stepTax) {
            $taxPayable = $minimumTax;
        }
        $taxPayable = round($taxPayable, 2);

        // 9. หักภาษีที่ชำระไว้แล้ว เพื่อหายอดชำระเพิ่มหรือขอคืน
        $withholdingTax = (float) $request->input('withholding_tax');
        $halfYearTax = (float) $request->input('half_year_tax');
        $taxPaid = round($withholdingTax + $halfYearTax, 2);
        $additionalTax = 0;
        $refund = 0;
        if ($taxPayable >= $taxPaid) {
            $additionalTax = round($taxPayable - $taxPaid, 2);
        } else {
            $refund = round($taxPaid - $taxPayable, 2);
        }

        // 10. เก็บผลล่าสุดใน session เพื่อให้สมาชิกเลือกบันทึกเป็นประวัติได้
        $form = $request->except('_token');
        $calculation = [
            'form' => $form,
            'total_income' => $totalIncomeBeforeExempt,
            'netIncome' => $netIncome,
            'taxPayable' => $taxPayable,
            'taxPaid' => $taxPaid,
            'additionalTax' => $additionalTax,
            'refund' => $refund,
            'expense' => $expense,
            'deduction' => $deduction,
            'donation' => $donation,
            'exemptIncome' => $exemptIncome,
            'stepTax' => $stepTax,
            'minimumTax' => $minimumTax,
            'expense_data' => [
                1 => $inc_g1 > 0 ? $expense12 : 0, 2 => $inc_g1 <= 0 ? $expense12 : 0, 3 => $expense3, 4 => 0,
                5 => $expense5, 6 => $inc_g6 * ($professionExpense === null ? 0 : (float) $professionExpense->rate),
                7 => $inc_g7 * ($constructionExpense === null ? 0 : (float) $constructionExpense->rate), 8 => $expense8,
            ],
            'deduction_data' => [
                'personal' => $personalType === null ? 0 : (float) $personalType->max_amount,
                'spouse' => $request->input('married-status') === 'married_no_income' && $spouseType !== null ? (float) $spouseType->max_amount : 0,
                'child' => $childCount * ($childType === null ? 0 : (float) $childType->max_amount),
                'child_bonus' => $childBonusCount * ($childBonusType === null ? 0 : (float) $childBonusType->max_amount),
                'adopted_child' => $adoptedCount * ($adoptedChildType === null ? 0 : (float) $adoptedChildType->max_amount),
                'disabled_care' => $careCount * ($careType === null ? 0 : (float) $careType->max_amount),
                'social_security' => (float) $request->input('deduct_social'),
                'life_insurance' => $life,
                'health_insurance' => $health,
                'donation' => (float) $request->input('deduct_donation'),
            ],
        ];
        $request->session()->put('tax_calculation', $calculation);

        // ส่งผลและข้อมูลเดิมกลับไปแสดงในฟอร์ม
        return view('calculate', compact('netIncome', 'taxPayable', 'withholdingTax', 'halfYearTax',
            'taxPaid', 'additionalTax', 'refund', 'expense', 'deduction', 'donation', 'exemptIncome',
            'stepTax', 'minimumTax', 'form', 'incomeTypes'));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
        ]);
        $calculation = $request->session()->get('tax_calculation');
        if ($calculation === null) {
            return redirect()->route('tax.show')->withErrors([
                'calculation' => 'กรุณาคำนวณภาษีก่อนบันทึกผล',
            ]);
        }

        // สร้าง Model แล้วใส่ข้อมูลทีละช่องเหมือนที่เรียนใน Laravel
        $form = $calculation['form'];
        $record = new TaxRecord();
        $record->user_id = $request->user()->id;
        $record->tax_year = $form['tax_year'];
        $record->title = $validated['title'] ?: 'ผลคำนวณภาษีปี '.$form['tax_year'];
        $record->married_status = $form['married-status'];
        $record->personal_details = [
            'child_count' => $form['child_count'],
            'child_bonus_count' => $form['child_bonus_count'],
            'adopted_child_count' => $form['adopted_child_count'],
            'disabled_family_count' => $form['disabled_family_count'],
            'disabled_other_count' => $form['disabled_other_count'],
        ];
        $record->total_income = $calculation['total_income'];
        $record->exempt_income = $calculation['exemptIncome'];
        $record->total_expense = $calculation['expense'];
        $record->total_deduction = $calculation['deduction'];
        $record->donation_allowed = $calculation['donation'];
        $record->net_income = $calculation['netIncome'];
        $record->step_tax = $calculation['stepTax'];
        $record->minimum_tax = $calculation['minimumTax'];
        $record->tax_payable = $calculation['taxPayable'];
        $record->withholding_tax = $form['withholding_tax'];
        $record->half_year_tax = $form['half_year_tax'];
        $record->additional_tax = $calculation['additionalTax'];
        $record->refund = $calculation['refund'];
        $record->calculation_version = '2';
        $record->save();

        // เก็บรายการเงินได้แต่ละประเภท เพื่อให้ผู้ใช้แก้ไขทีละรายการในภายหลังได้
        $savedIncomes = [];
        for ($group = 1; $group <= 8; $group++) {
            $amount = (float) $form['income_g'.$group];
            if ($amount > 0) {
                $incomeType = IncomeType::where('code', '40_'.$group)->first();
                $income = new Income();
                $income->tax_record_id = $record->id;
                $income->income_type_id = $incomeType?->id;
                $income->type_name = $incomeType?->name ?? 'เงินได้ ม.40('.$group.')';
                $income->amount = $amount;
                $income->save();
                $savedIncomes[$group] = $income;
            }
        }

        // แยกค่าใช้จ่ายตามประเภทเงินได้ ไม่เก็บเป็นยอดรวมก้อนเดียว
        foreach ($calculation['expense_data'] as $group => $allowedAmount) {
            if ($allowedAmount <= 0 || !isset($savedIncomes[$group])) { continue; }
            $expenseCode = null;
            if ($group === 1) { $expenseCode = 'salary_flat'; }
            if ($group === 2) { $expenseCode = 'commission_flat'; }
            if ($group === 3) { $expenseCode = 'rights_flat'; }
            if ($group === 5) {
                $rentalCodes = ['building' => 'building_flat', 'agriculture_land' => 'agriculture_land_flat', 'other_land' => 'other_land_flat', 'vehicle' => 'vehicle_flat', 'other_property' => 'other_property_flat'];
                $expenseCode = $form['income_g5_method'] === 'actual' ? 'rental_actual' : ($rentalCodes[$form['income_g5_type']] ?? null);
            }
            if ($group === 6) { $expenseCode = $form['income_g6_type'] === 'medical' ? 'medical_flat' : 'profession_other_flat'; }
            if ($group === 7) { $expenseCode = 'construction_flat'; }
            if ($group === 8) { $expenseCode = $form['income_g8_method'] === 'actual' ? 'business_actual' : ($form['income_g8_type'] === 'resale' ? 'resale_flat' : 'restaurant_flat'); }
            $expenseType = ExpenseType::where('tax_year', $record->tax_year)->where('code', $expenseCode)->first();
            $expenseItem = new Expense();
            $expenseItem->income_id = $savedIncomes[$group]->id;
            $expenseItem->expense_type_id = $expenseType?->id;
            $expenseItem->type_name = $expenseType?->name ?? 'ค่าใช้จ่ายจากการคำนวณ';
            $expenseItem->method = $expenseType?->method ?? 'actual';
            $expenseItem->rate = $expenseType?->rate;
            $expenseItem->amount = $allowedAmount;
            $expenseItem->allowed_amount = $allowedAmount;
            $expenseItem->save();
        }

        // แยกค่าลดหย่อนแต่ละประเภทตามข้อมูลที่ผู้ใช้กรอก
        foreach ($calculation['deduction_data'] as $code => $amount) {
            if ($amount <= 0) { continue; }
            $deductionType = DeductionType::where('tax_year', $record->tax_year)->where('code', $code)->first();
            if ($deductionType === null) { continue; }
            $deductionItem = new Deduction();
            $deductionItem->tax_record_id = $record->id;
            $deductionItem->deduction_type_id = $deductionType->id;
            $deductionItem->type_name = $deductionType->name;
            $deductionItem->amount = $amount;
            $deductionItem->allowed_amount = $code === 'donation' ? $calculation['donation'] : $amount;
            $deductionItem->save();
        }

        $request->session()->forget('tax_calculation');
        return redirect()->route('tax.records.show', $record)
            ->with('success', 'บันทึกผลคำนวณเรียบร้อยแล้ว');
    }
}
