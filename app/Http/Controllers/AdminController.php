<?php

namespace App\Http\Controllers;

use App\Models\TaxRecord;
use App\Models\User;
use App\Models\IncomeType;
use App\Models\ExpenseType;
use App\Models\DeductionType;
use App\Models\TaxBracket;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $userCount = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $taxRecordCount = TaxRecord::count();

        return view('admin.dashboard', compact('userCount', 'adminCount', 'taxRecordCount'));
    }

    public function users()
    {
        $users = User::orderBy('id')->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
        ], [
            'role.required' => 'กรุณาเลือกสิทธิผู้ใช้',
            'role.in' => 'สิทธิผู้ใช้ต้องเป็น User หรือ Admin',
        ]);

        // ไม่ลดสิทธิของตนเอง ป้องกันระบบไม่มีผู้ดูแลเหลืออยู่
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['role' => 'ไม่สามารถเปลี่ยนสิทธิของตนเองได้']);
        }

        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.users')
            ->with('success', 'เปลี่ยนสิทธิผู้ใช้เรียบร้อยแล้ว');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);
        $user->name = $validated['name'];
        $user->email = strtolower(trim($validated['email']));
        $user->save();
        return back()->with('success', 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'ไม่สามารถลบบัญชีของตนเองได้']);
        }
        $user->delete();
        return back()->with('success', 'ลบบัญชีผู้ใช้เรียบร้อยแล้ว');
    }

    public function incomeTypes()
    {
        $items = IncomeType::orderBy('code')->get();
        return view('admin.income-types', compact('items'));
    }

    public function storeIncomeType(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:income_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $item = new IncomeType();
        $item->code = $validated['code'];
        $item->name = $validated['name'];
        $item->description = $validated['description'] ?? null;
        $item->is_active = true;
        $item->save();
        return back()->with('success', 'เพิ่มประเภทเงินได้แล้ว');
    }

    public function updateIncomeType(Request $request, IncomeType $incomeType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);
        $incomeType->name = $validated['name'];
        $incomeType->description = $validated['description'] ?? null;
        $incomeType->is_active = $validated['is_active'];
        $incomeType->save();
        return back()->with('success', 'แก้ไขประเภทเงินได้แล้ว');
    }

    public function expenseTypes()
    {
        $items = ExpenseType::with('incomeType')->orderByDesc('tax_year')->orderBy('code')->get();
        $incomeTypes = IncomeType::orderBy('code')->get();
        return view('admin.expense-types', compact('items', 'incomeTypes'));
    }

    public function storeExpenseType(Request $request)
    {
        $validated = $request->validate([
            'income_type_id' => ['required', 'exists:income_types,id'],
            'code' => ['required', 'string', 'max:50'], 'name' => ['required', 'string', 'max:255'],
            'tax_year' => ['required', 'integer', 'min:2500', 'max:2600'],
            'method' => ['required', 'in:flat,actual'], 'rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'max_amount' => ['nullable', 'numeric', 'min:0'], 'description' => ['nullable', 'string'],
        ]);
        $request->validate(['code' => ['unique:expense_types,code,NULL,id,tax_year,'.$validated['tax_year']]]);
        $item = new ExpenseType();
        $item->income_type_id = $validated['income_type_id'];
        $item->code = $validated['code'];
        $item->name = $validated['name'];
        $item->tax_year = $validated['tax_year'];
        $item->method = $validated['method'];
        $item->rate = $validated['rate'];
        $item->max_amount = $validated['max_amount'];
        $item->description = $validated['description'];
        $item->is_active = true;
        $item->save();
        return back()->with('success', 'เพิ่มประเภทค่าใช้จ่ายแล้ว');
    }

    public function updateExpenseType(Request $request, ExpenseType $expenseType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'max_amount' => ['nullable', 'numeric', 'min:0'], 'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);
        $expenseType->name = $validated['name'];
        $expenseType->rate = $validated['rate'];
        $expenseType->max_amount = $validated['max_amount'];
        $expenseType->description = $validated['description'];
        $expenseType->is_active = $validated['is_active'];
        $expenseType->save();
        return back()->with('success', 'แก้ไขประเภทค่าใช้จ่ายแล้ว');
    }

    public function deductionTypes()
    {
        $items = DeductionType::orderByDesc('tax_year')->orderBy('code')->get();
        return view('admin.deduction-types', compact('items'));
    }

    public function storeDeductionType(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'], 'name' => ['required', 'string', 'max:255'],
            'tax_year' => ['required', 'integer', 'min:2500', 'max:2600'],
            'max_amount' => ['nullable', 'numeric', 'min:0'], 'description' => ['nullable', 'string'],
        ]);
        $request->validate(['code' => ['unique:deduction_types,code,NULL,id,tax_year,'.$validated['tax_year']]]);
        $item = new DeductionType();
        $item->code = $validated['code'];
        $item->name = $validated['name'];
        $item->tax_year = $validated['tax_year'];
        $item->max_amount = $validated['max_amount'];
        $item->description = $validated['description'];
        $item->is_active = true;
        $item->save();
        return back()->with('success', 'เพิ่มประเภทค่าลดหย่อนแล้ว');
    }

    public function updateDeductionType(Request $request, DeductionType $deductionType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'max_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'], 'is_active' => ['required', 'boolean'],
        ]);
        $deductionType->name = $validated['name'];
        $deductionType->max_amount = $validated['max_amount'];
        $deductionType->description = $validated['description'];
        $deductionType->is_active = $validated['is_active'];
        $deductionType->save();
        return back()->with('success', 'แก้ไขประเภทค่าลดหย่อนแล้ว');
    }

    public function taxBrackets()
    {
        $items = TaxBracket::orderByDesc('tax_year')->orderBy('lower_bound')->get();
        return view('admin.tax-brackets', compact('items'));
    }

    public function storeTaxBracket(Request $request)
    {
        $validated = $request->validate([
            'tax_year' => ['required', 'integer', 'min:2500', 'max:2600'],
            'lower_bound' => ['required', 'numeric', 'min:0'], 'upper_bound' => ['nullable', 'numeric', 'gt:lower_bound'],
            'rate' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);
        $request->validate(['lower_bound' => ['unique:tax_brackets,lower_bound,NULL,id,tax_year,'.$validated['tax_year']]]);
        $item = new TaxBracket();
        $item->tax_year = $validated['tax_year'];
        $item->lower_bound = $validated['lower_bound'];
        $item->upper_bound = $validated['upper_bound'];
        $item->rate = $validated['rate'];
        $item->save();
        return back()->with('success', 'เพิ่มขั้นภาษีแล้ว');
    }

    public function updateTaxBracket(Request $request, TaxBracket $taxBracket)
    {
        $validated = $request->validate([
            'upper_bound' => ['nullable', 'numeric', 'gt:'.$taxBracket->lower_bound],
            'rate' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);
        $taxBracket->upper_bound = $validated['upper_bound'];
        $taxBracket->rate = $validated['rate'];
        $taxBracket->save();
        return back()->with('success', 'แก้ไขขั้นภาษีแล้ว');
    }
}
