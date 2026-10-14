<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\DeductionType;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\TaxBracket;
use App\Models\TaxRecord;
use Illuminate\Http\Request;

class TaxRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = $request->user()->taxRecords()->latest()->paginate(10);
        return view('member.tax-records', compact('records'));
    }

    public function show(Request $request, TaxRecord $record)
    {
        if ($record->user_id !== $request->user()->id) { abort(403); }
        $record->load('incomes.incomeType', 'incomes.expenses.expenseType', 'deductions.deductionType');
        $incomeTypes = IncomeType::where('is_active', true)->orderBy('code')->get();
        $expenseTypes = ExpenseType::with('incomeType')->where('tax_year', $record->tax_year)->where('is_active', true)->orderBy('name')->get();
        $deductionTypes = DeductionType::where('tax_year', $record->tax_year)->where('is_active', true)->orderBy('name')->get();
        return view('member.tax-record-show', compact('record', 'incomeTypes', 'expenseTypes', 'deductionTypes'));
    }

    public function storeIncome(Request $request, TaxRecord $record)
    {
        if ($record->user_id !== $request->user()->id) { abort(403); }
        $validated = $request->validate(['income_type_id' => ['required', 'exists:income_types,id'], 'amount' => ['required', 'numeric', 'min:0']]);
        $incomeType = IncomeType::find($validated['income_type_id']);
        $income = new Income();
        $income->tax_record_id = $record->id;
        $income->income_type_id = $incomeType->id;
        $income->type_name = $incomeType->name;
        $income->amount = $validated['amount'];
        $income->save();
        $this->calculateRecord($record);
        return back()->with('success', 'เพิ่มรายการเงินได้เรียบร้อยแล้ว');
    }

    public function updateIncome(Request $request, TaxRecord $record, Income $income)
    {
        if ($record->user_id !== $request->user()->id || $income->tax_record_id !== $record->id) { abort(403); }
        $validated = $request->validate(['income_type_id' => ['required', 'exists:income_types,id'], 'amount' => ['required', 'numeric', 'min:0']]);
        $incomeType = IncomeType::find($validated['income_type_id']);
        $currentExpense = $income->expenses()->sum('allowed_amount');
        if ($validated['amount'] < $currentExpense) {
            return back()->withErrors(['amount' => 'รายได้ต้องไม่น้อยกว่าค่าใช้จ่ายที่หักได้ของรายการนี้']);
        }
        $income->income_type_id = $incomeType->id;
        $income->type_name = $incomeType->name;
        $income->amount = $validated['amount'];
        $income->save();
        $this->calculateRecord($record);
        return back()->with('success', 'แก้ไขรายการเงินได้เรียบร้อยแล้ว');
    }

    public function destroyIncome(Request $request, TaxRecord $record, Income $income)
    {
        if ($record->user_id !== $request->user()->id || $income->tax_record_id !== $record->id) { abort(403); }
        $income->delete();
        $this->calculateRecord($record);
        return back()->with('success', 'ลบรายการเงินได้เรียบร้อยแล้ว');
    }

    public function storeExpense(Request $request, TaxRecord $record)
    {
        if ($record->user_id !== $request->user()->id) { abort(403); }
        $validated = $request->validate([
            'income_id' => ['required', 'exists:incomes,id'],
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);
        $income = Income::find($validated['income_id']);
        if ($income->tax_record_id !== $record->id) { abort(403); }
        $expenseType = ExpenseType::find($validated['expense_type_id']);
        if ($expenseType->tax_year !== $record->tax_year || $expenseType->income_type_id !== $income->income_type_id || !$expenseType->is_active) {
            return back()->withErrors(['expense_type_id' => 'ประเภทค่าใช้จ่ายไม่ตรงกับรายการเงินได้หรือปีภาษี']);
        }

        $amount = (float) ($validated['amount'] ?? 0);
        $allowedAmount = $amount;
        if ($expenseType->method === 'flat') {
            $allowedAmount = (float) $income->amount * (float) $expenseType->rate;
            $amount = $allowedAmount;
        }
        if ($expenseType->max_amount !== null && $allowedAmount > $expenseType->max_amount) {
            $allowedAmount = (float) $expenseType->max_amount;
        }
        if ($allowedAmount > $income->amount) {
            $allowedAmount = (float) $income->amount;
        }
        $usedExpense = $income->expenses()->sum('allowed_amount');
        $remainingExpense = (float) $income->amount - $usedExpense;
        if ($allowedAmount > $remainingExpense) {
            $allowedAmount = max(0, $remainingExpense);
        }

        $expense = new Expense();
        $expense->income_id = $income->id;
        $expense->expense_type_id = $expenseType->id;
        $expense->type_name = $expenseType->name;
        $expense->method = $expenseType->method;
        $expense->rate = $expenseType->rate;
        $expense->amount = $amount;
        $expense->allowed_amount = $allowedAmount;
        $expense->save();
        $this->calculateRecord($record);
        return back()->with('success', 'เพิ่มรายการค่าใช้จ่ายเรียบร้อยแล้ว');
    }

    public function updateExpense(Request $request, TaxRecord $record, Expense $expense)
    {
        if ($record->user_id !== $request->user()->id || $expense->income->tax_record_id !== $record->id) { abort(403); }
        $validated = $request->validate(['amount' => ['nullable', 'numeric', 'min:0']]);
        $expenseType = $expense->expenseType;
        $amount = (float) ($validated['amount'] ?? 0);
        $allowedAmount = $amount;
        if ($expenseType !== null && $expenseType->method === 'flat') {
            $allowedAmount = (float) $expense->income->amount * (float) $expenseType->rate;
            $amount = $allowedAmount;
        }
        if ($expenseType !== null && $expenseType->max_amount !== null && $allowedAmount > $expenseType->max_amount) {
            $allowedAmount = (float) $expenseType->max_amount;
        }
        if ($allowedAmount > $expense->income->amount) {
            $allowedAmount = (float) $expense->income->amount;
        }
        $usedExpense = $expense->income->expenses()->where('id', '!=', $expense->id)->sum('allowed_amount');
        $remainingExpense = (float) $expense->income->amount - $usedExpense;
        if ($allowedAmount > $remainingExpense) {
            $allowedAmount = max(0, $remainingExpense);
        }
        $expense->amount = $amount;
        $expense->allowed_amount = $allowedAmount;
        $expense->save();
        $this->calculateRecord($record);
        return back()->with('success', 'แก้ไขรายการค่าใช้จ่ายเรียบร้อยแล้ว');
    }

    public function destroyExpense(Request $request, TaxRecord $record, Expense $expense)
    {
        if ($record->user_id !== $request->user()->id || $expense->income->tax_record_id !== $record->id) { abort(403); }
        $expense->delete();
        $this->calculateRecord($record);
        return back()->with('success', 'ลบรายการค่าใช้จ่ายเรียบร้อยแล้ว');
    }

    public function storeDeduction(Request $request, TaxRecord $record)
    {
        if ($record->user_id !== $request->user()->id) { abort(403); }
        $validated = $request->validate([
            'deduction_type_id' => ['required', 'exists:deduction_types,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);
        $deductionType = DeductionType::find($validated['deduction_type_id']);
        if ($deductionType->tax_year !== $record->tax_year || !$deductionType->is_active) {
            return back()->withErrors(['deduction_type_id' => 'ประเภทค่าลดหย่อนไม่ตรงกับปีภาษี']);
        }
        $allowedAmount = (float) $validated['amount'];
        if ($deductionType->max_amount !== null && $allowedAmount > $deductionType->max_amount) {
            $allowedAmount = (float) $deductionType->max_amount;
        }
        $deduction = new Deduction();
        $deduction->tax_record_id = $record->id;
        $deduction->deduction_type_id = $deductionType->id;
        $deduction->type_name = $deductionType->name;
        $deduction->amount = $validated['amount'];
        $deduction->allowed_amount = $allowedAmount;
        $deduction->save();
        $this->calculateRecord($record);
        return back()->with('success', 'เพิ่มรายการค่าลดหย่อนเรียบร้อยแล้ว');
    }

    public function updateDeduction(Request $request, TaxRecord $record, Deduction $deduction)
    {
        if ($record->user_id !== $request->user()->id || $deduction->tax_record_id !== $record->id) { abort(403); }
        $validated = $request->validate(['amount' => ['required', 'numeric', 'min:0']]);
        $deductionType = $deduction->deductionType;
        $allowedAmount = (float) $validated['amount'];
        if ($deductionType !== null && $deductionType->max_amount !== null && $allowedAmount > $deductionType->max_amount) {
            $allowedAmount = (float) $deductionType->max_amount;
        }
        $deduction->amount = $validated['amount'];
        $deduction->allowed_amount = $allowedAmount;
        $deduction->save();
        $this->calculateRecord($record);
        return back()->with('success', 'แก้ไขรายการค่าลดหย่อนเรียบร้อยแล้ว');
    }

    public function destroyDeduction(Request $request, TaxRecord $record, Deduction $deduction)
    {
        if ($record->user_id !== $request->user()->id || $deduction->tax_record_id !== $record->id) { abort(403); }
        $deduction->delete();
        $this->calculateRecord($record);
        return back()->with('success', 'ลบรายการค่าลดหย่อนเรียบร้อยแล้ว');
    }

    // รวมยอดและภาษีใหม่ เมื่อผู้ใช้แก้ไขรายการ
    public function calculateRecord(TaxRecord $record)
    {
        $totalIncome = $record->incomes()->sum('amount');
        $incomeIds = $record->incomes()->pluck('id');
        $totalExpense = Expense::whereIn('income_id', $incomeIds)->sum('allowed_amount');
        $deductions = $record->deductions()->with('deductionType')->get();
        $totalDeduction = 0;
        $donationRequested = 0;
        foreach ($deductions as $deduction) {
            if ($deduction->deductionType?->code === 'donation') {
                $donationRequested += (float) $deduction->amount;
            } else {
                $totalDeduction += (float) $deduction->allowed_amount;
            }
        }
        $beforeDonation = $totalIncome - $record->exempt_income - $totalExpense - $totalDeduction;
        if ($beforeDonation < 0) { $beforeDonation = 0; }
        $donationAllowed = min($donationRequested, $beforeDonation * 0.10);
        $remainingDonation = $donationAllowed;
        foreach ($deductions as $deduction) {
            if ($deduction->deductionType?->code === 'donation') {
                $allowed = min((float) $deduction->amount, $remainingDonation);
                $deduction->allowed_amount = $allowed;
                $deduction->save();
                $remainingDonation -= $allowed;
            }
        }
        $netIncome = $beforeDonation - $donationAllowed;

        if ($netIncome < 0) { $netIncome = 0; }
        $stepTax = 0;
        $brackets = TaxBracket::where('tax_year', $record->tax_year)->orderBy('lower_bound')->get();
        foreach ($brackets as $bracket) {
            if ($netIncome > $bracket->lower_bound) {
                $upper = $bracket->upper_bound === null ? $netIncome : $bracket->upper_bound;
                $stepTax += (min($netIncome, $upper) - $bracket->lower_bound) * $bracket->rate;
            }
        }

        $nonSalaryIncome = $record->incomes()->whereHas('incomeType', function ($query) { $query->where('code', '!=', '40_1'); })->sum('amount');
        $minimumTax = $nonSalaryIncome >= 120000 ? $nonSalaryIncome * 0.005 : 0;
        $taxPayable = ($minimumTax > 5000 && $minimumTax > $stepTax) ? $minimumTax : $stepTax;
        $taxPaid = $record->withholding_tax + $record->half_year_tax;
        
        $record->total_income = $totalIncome;
        $record->total_expense = $totalExpense;
        $record->total_deduction = $totalDeduction;
        $record->donation_allowed = $donationAllowed;
        $record->net_income = $netIncome;
        $record->step_tax = $stepTax;
        $record->minimum_tax = $minimumTax;
        $record->tax_payable = $taxPayable;
        $record->additional_tax = $taxPayable >= $taxPaid ? $taxPayable - $taxPaid : 0;
        $record->refund = $taxPaid > $taxPayable ? $taxPaid - $taxPayable : 0;
        $record->save();
    }

    public function destroy(Request $request, TaxRecord $record)
    {
        if ($record->user_id !== $request->user()->id) { abort(403); }
        $record->delete();
        return redirect()->route('tax.records.index')->with('success', 'ลบประวัติผลคำนวณเรียบร้อยแล้ว');
    }
}
