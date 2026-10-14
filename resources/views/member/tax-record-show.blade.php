<!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $record->title }}</title><link rel="stylesheet" href="{{ asset('css/base.css') }}"><link rel="stylesheet" href="{{ asset('css/tax-record-show.css') }}"></head>
<body>
<header><h1>Personal Tax</h1><nav><a href="{{ route('tax.records.index') }}">ประวัติผลคำนวณ</a><a href="{{ route('member.index') }}">ส่วนสมาชิก</a><a href="{{ route('tax.show') }}">คำนวณภาษี</a></nav></header>
<main class="member-panel">
<h2>{{ $record->title }}</h2><p>ปีภาษี {{ $record->tax_year }} / บันทึกเมื่อ {{ $record->created_at->format('d/m/Y H:i') }}</p>
@if(session('success'))<p class="message">{{ session('success') }}</p>@endif
@if($errors->any())<div class="message error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<section class="result-summary"><p>เงินได้รวม <strong>{{ number_format($record->total_income, 2) }}</strong> บาท</p><p>ค่าใช้จ่าย <strong>{{ number_format($record->total_expense, 2) }}</strong> บาท</p><p>ค่าลดหย่อน <strong>{{ number_format($record->total_deduction, 2) }}</strong> บาท</p><p>เงินได้สุทธิ <strong>{{ number_format($record->net_income, 2) }}</strong> บาท</p><p>ภาษีทั้งปี <strong>{{ number_format($record->tax_payable, 2) }}</strong> บาท</p></section>

<section class="item-section"><h3>รายการเงินได้</h3>
<form action="{{ route('tax.incomes.store', $record) }}" method="POST" class="item-form">@csrf
<select name="income_type_id" required><option value="">เลือกประเภทเงินได้</option>@foreach($incomeTypes as $type)<option value="{{ $type->id }}">{{ $type->code }} - {{ $type->name }}</option>@endforeach</select><input type="number" name="amount" min="0" step="0.01" placeholder="จำนวนเงิน" required><button>เพิ่มเงินได้</button></form>
@forelse($record->incomes as $income)
<form action="{{ route('tax.incomes.update', [$record, $income]) }}" method="POST" class="item-form">@csrf @method('PUT')<select name="income_type_id">@foreach($incomeTypes as $type)<option value="{{ $type->id }}" @selected($income->income_type_id === $type->id)>{{ $type->code }} - {{ $type->name }}</option>@endforeach</select><input type="number" name="amount" min="0" step="0.01" value="{{ $income->amount }}"><button>แก้ไข</button></form>
<form action="{{ route('tax.incomes.destroy', [$record, $income]) }}" method="POST" class="delete-form">@csrf @method('DELETE')<button>ลบเงินได้</button></form>
<div class="sub-items"><p>ค่าใช้จ่ายของ: {{ $income->type_name }}</p>@foreach($income->expenses as $expense)
<form action="{{ route('tax.expenses.update', [$record, $expense]) }}" method="POST" class="item-form">@csrf @method('PUT')<strong>{{ $expense->type_name }}</strong><span>หักได้ {{ number_format($expense->allowed_amount, 2) }} บาท</span><input type="number" name="amount" min="0" step="0.01" value="{{ $expense->amount }}" @if($expense->method === 'flat') readonly @endif><button>แก้ไข</button></form>
<form action="{{ route('tax.expenses.destroy', [$record, $expense]) }}" method="POST" class="delete-form">@csrf @method('DELETE')<button>ลบค่าใช้จ่าย</button></form>@endforeach</div>
@empty<p>ยังไม่มีรายการเงินได้</p>@endforelse</section>

<section class="item-section"><h3>เพิ่มค่าใช้จ่าย</h3>@if($record->incomes->count() > 0)
<form action="{{ route('tax.expenses.store', $record) }}" method="POST" class="item-form">@csrf<select name="income_id" required>@foreach($record->incomes as $income)<option value="{{ $income->id }}">{{ $income->type_name }}</option>@endforeach</select><select name="expense_type_id" required><option value="">เลือกประเภทค่าใช้จ่าย</option>@foreach($expenseTypes as $type)<option value="{{ $type->id }}">{{ $type->incomeType->code }} - {{ $type->name }}</option>@endforeach</select><input type="number" name="amount" min="0" step="0.01" placeholder="ยอดจ่ายจริง (แบบเหมาเว้นว่างได้)"><button>เพิ่มค่าใช้จ่าย</button></form>
@else<p>เพิ่มรายการเงินได้ก่อน จึงจะเพิ่มค่าใช้จ่ายได้</p>@endif</section>

<section class="item-section"><h3>รายการค่าลดหย่อน</h3>
<form action="{{ route('tax.deductions.store', $record) }}" method="POST" class="item-form">@csrf<select name="deduction_type_id" required><option value="">เลือกประเภทค่าลดหย่อน</option>@foreach($deductionTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select><input type="number" name="amount" min="0" step="0.01" placeholder="ยอดจ่ายจริง" required><button>เพิ่มค่าลดหย่อน</button></form>
@forelse($record->deductions as $deduction)<form action="{{ route('tax.deductions.update', [$record, $deduction]) }}" method="POST" class="item-form">@csrf @method('PUT')<strong>{{ $deduction->type_name }}</strong><span>หักได้ {{ number_format($deduction->allowed_amount, 2) }} บาท</span><input type="number" name="amount" min="0" step="0.01" value="{{ $deduction->amount }}"><button>แก้ไข</button></form><form action="{{ route('tax.deductions.destroy', [$record, $deduction]) }}" method="POST" class="delete-form">@csrf @method('DELETE')<button>ลบค่าลดหย่อน</button></form>@empty<p>ยังไม่มีรายการค่าลดหย่อนที่เพิ่มเอง</p>@endforelse</section>
<form action="{{ route('tax.records.destroy', $record) }}" method="POST" class="delete-form">@csrf @method('DELETE')<button onclick="return confirm('ต้องการลบประวัติรายการนี้หรือไม่')">ลบประวัติรายการนี้</button></form>
</main></body></html>
