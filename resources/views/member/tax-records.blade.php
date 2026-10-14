<!DOCTYPE html>
<html lang="th">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>ประวัติผลคำนวณ</title><link rel="stylesheet" href="{{ asset('css/base.css') }}"><link rel="stylesheet" href="{{ asset('css/tax-records.css') }}"></head>
<body>
    <header><h1>Personal Tax</h1><nav><a href="{{ route('member.index') }}">ส่วนสมาชิก</a><a href="{{ route('tax.show') }}">คำนวณภาษี</a><a href="{{ route('profile.edit') }}">แก้ไขข้อมูลส่วนตัว</a></nav></header>
    <main class="member-panel">
        <h2>ประวัติผลคำนวณ</h2>
        @if(session('success'))<p class="message">{{ session('success') }}</p>@endif
        @if($records->count() === 0)
            <p>ยังไม่มีรายการที่บันทึกไว้</p>
        @else
            <table><thead><tr><th>ชื่อรายการ</th><th>ปีภาษี</th><th>ภาษีทั้งปี</th><th>วันที่บันทึก</th><th></th></tr></thead>
            <tbody>@foreach($records as $record)<tr><td>{{ $record->title }}</td><td>{{ $record->tax_year }}</td><td>{{ number_format($record->tax_payable, 2) }}</td><td>{{ $record->created_at->format('d/m/Y H:i') }}</td><td><a href="{{ route('tax.records.show', $record) }}">ดูรายละเอียด</a></td></tr>@endforeach</tbody></table>
        @endif
    </main>
</body>
</html>
