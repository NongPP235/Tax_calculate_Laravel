<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการระบบ - Personal Tax</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
</head>
<body>
    <header>
        <h1>Personal Tax</h1>
        <nav>
            <a href="{{ route('home') }}">หน้าหลัก</a>
            <a href="{{ route('tax.show') }}">คำนวณภาษี</a>
            <a href="{{ route('member.index') }}">ส่วนสมาชิก</a>
            <a href="{{ route('profile.edit') }}">โปรไฟล์ของฉัน</a>
            @if(Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}">จัดการระบบ</a>
                <a href="{{ route('admin.users') }}">จัดการสิทธิผู้ใช้</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">ออกจากระบบ</button>
            </form>
        </nav>
    </header>
    <main class="dashboard-page admin-dashboard">
        @if(session('success'))
            <p class="message" role="status">{{ session('success') }}</p>
        @endif
        @if($errors->any())
            <div class="message error" role="alert">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <section class="dashboard-hero admin-hero">
            <div><p class="eyebrow">ADMIN DASHBOARD</p><h2>ภาพรวมระบบ</h2><p>จัดการข้อมูลอ้างอิงภาษี ผู้ใช้ และตรวจจำนวนการใช้งานของระบบ</p></div>
            <div class="profile-badge">⚙</div>
        </section>
        <section class="statistics dashboard-stats">
            <div><span>บัญชีทั้งหมด</span><strong>{{ $userCount }}</strong><small>บัญชี</small></div>
            <div><span>ผู้ดูแลระบบ</span><strong>{{ $adminCount }}</strong><small>บัญชี</small></div>
            <div><span>ผลคำนวณที่บันทึก</span><strong>{{ $taxRecordCount }}</strong><small>รายการ</small></div>
        </section>
        <section class="dashboard-grid admin-grid">
            <a class="dashboard-action" href="{{ route('admin.users') }}"><span>👥</span><div><h3>จัดการผู้ใช้</h3><p>ดูรายชื่อและเปลี่ยนสิทธิ์</p></div></a>
            <a class="dashboard-action" href="{{ route('admin.income-types') }}"><span>💰</span><div><h3>ประเภทเงินได้</h3><p>เพิ่มหรือปิดการใช้งานประเภทเงินได้</p></div></a>
            <a class="dashboard-action" href="{{ route('admin.expense-types') }}"><span>🧾</span><div><h3>ประเภทค่าใช้จ่าย</h3><p>จัดการอัตราและเพดานหักค่าใช้จ่าย</p></div></a>
            <a class="dashboard-action" href="{{ route('admin.deduction-types') }}"><span>➖</span><div><h3>ประเภทค่าลดหย่อน</h3><p>จัดการรายการลดหย่อนและเพดาน</p></div></a>
            <a class="dashboard-action" href="{{ route('admin.tax-brackets') }}"><span>📊</span><div><h3>ขั้นภาษี</h3><p>จัดการช่วงเงินได้และอัตราภาษี</p></div></a>
            <a class="dashboard-action admin" href="{{ route('profile.edit') }}"><span>👤</span><div><h3>โปรไฟล์ของฉัน</h3><p>แก้ไขข้อมูลบัญชีผู้ดูแล</p></div></a>
        </section>
    </main>
</body>
</html>
