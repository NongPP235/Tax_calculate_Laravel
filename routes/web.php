<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaxCalculationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaxRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class,'index'])->name('home');
Route::get('/about', [HomeController::class,'about']);
Route::get('/knowledge', [HomeController::class,'knowledge']);

Route::get('/TaxCalculate', [TaxCalculationController::class,'show']) -> name('tax.show');
Route::post('/TaxCalculate', [TaxCalculationController::class,'calculateTax'])-> name('tax.calculate');

// ผู้ที่ยังไม่ได้เข้าสู่ระบบ จึงจะเข้าหน้าสมัครและเข้าสู่ระบบได้
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.auth');
    Route::post('/login/fortify', [AuthController::class, 'authenticate'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])
        ->middleware('throttle:10,1')->name('register.store');
});

// ผู้ที่เข้าสู่ระบบแล้ว จึงจะออกจากระบบได้
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// User และ Admin เข้าถึงส่วนสมาชิกได้
Route::middleware(['auth', 'member'])->group(function () {
    Route::get('/member', [MemberController::class, 'index'])->name('member.index');
    Route::get('/dashboard', [MemberController::class, 'index'])->name('dashboard');
    Route::get('/member/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/member/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/TaxCalculate/save', [TaxCalculationController::class, 'save'])->name('tax.save');
    Route::get('/member/tax-records', [TaxRecordController::class, 'index'])->name('tax.records.index');
    Route::get('/member/tax-records/{record}', [TaxRecordController::class, 'show'])->name('tax.records.show');
    Route::delete('/member/tax-records/{record}', [TaxRecordController::class, 'destroy'])->name('tax.records.destroy');
    Route::post('/member/tax-records/{record}/incomes', [TaxRecordController::class, 'storeIncome'])->name('tax.incomes.store');
    Route::put('/member/tax-records/{record}/incomes/{income}', [TaxRecordController::class, 'updateIncome'])->name('tax.incomes.update');
    Route::delete('/member/tax-records/{record}/incomes/{income}', [TaxRecordController::class, 'destroyIncome'])->name('tax.incomes.destroy');
    Route::post('/member/tax-records/{record}/expenses', [TaxRecordController::class, 'storeExpense'])->name('tax.expenses.store');
    Route::put('/member/tax-records/{record}/expenses/{expense}', [TaxRecordController::class, 'updateExpense'])->name('tax.expenses.update');
    Route::delete('/member/tax-records/{record}/expenses/{expense}', [TaxRecordController::class, 'destroyExpense'])->name('tax.expenses.destroy');
    Route::post('/member/tax-records/{record}/deductions', [TaxRecordController::class, 'storeDeduction'])->name('tax.deductions.store');
    Route::put('/member/tax-records/{record}/deductions/{deduction}', [TaxRecordController::class, 'updateDeduction'])->name('tax.deductions.update');
    Route::delete('/member/tax-records/{record}/deductions/{deduction}', [TaxRecordController::class, 'destroyDeduction'])->name('tax.deductions.destroy');
});

// เก็บหน้า Livewire เดิมไว้ แต่ใช้ชื่อและ URL ที่ไม่ชนกับโปรไฟล์ Blade
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
    Route::livewire('/settings/security', 'pages::settings.security')
        ->middleware('password.confirm')->name('security.edit');
});

// Admin เท่านั้นที่เข้าถึงส่วนจัดการได้
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateRole'])->name('admin.users.role');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::get('/admin/income-types', [AdminController::class, 'incomeTypes'])->name('admin.income-types');
    Route::post('/admin/income-types', [AdminController::class, 'storeIncomeType'])->name('admin.income-types.store');
    Route::put('/admin/income-types/{incomeType}', [AdminController::class, 'updateIncomeType'])->name('admin.income-types.update');
    Route::get('/admin/expense-types', [AdminController::class, 'expenseTypes'])->name('admin.expense-types');
    Route::post('/admin/expense-types', [AdminController::class, 'storeExpenseType'])->name('admin.expense-types.store');
    Route::put('/admin/expense-types/{expenseType}', [AdminController::class, 'updateExpenseType'])->name('admin.expense-types.update');
    Route::get('/admin/deduction-types', [AdminController::class, 'deductionTypes'])->name('admin.deduction-types');
    Route::post('/admin/deduction-types', [AdminController::class, 'storeDeductionType'])->name('admin.deduction-types.store');
    Route::put('/admin/deduction-types/{deductionType}', [AdminController::class, 'updateDeductionType'])->name('admin.deduction-types.update');
    Route::get('/admin/tax-brackets', [AdminController::class, 'taxBrackets'])->name('admin.tax-brackets');
    Route::post('/admin/tax-brackets', [AdminController::class, 'storeTaxBracket'])->name('admin.tax-brackets.store');
    Route::put('/admin/tax-brackets/{taxBracket}', [AdminController::class, 'updateTaxBracket'])->name('admin.tax-brackets.update');
});
