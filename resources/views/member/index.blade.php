<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่วนสมาชิก - Personal Tax</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/member-dashboard.css') }}">
</head>
<body>
    <header>
        <h1>Personal Tax</h1>
        <nav>
            <a href="{{ route('home') }}">หน้าหลัก</a>
            <a href="{{ route('tax.show') }}">คำนวณภาษี</a>
            <a href="{{ route('member.index') }}">ส่วนสมาชิก</a>
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
    <main class="dashboard-page">
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
        <section class="dashboard-hero">
            <div>
                <p class="eyebrow">MEMBER DASHBOARD</p>
                <h2>สวัสดี, {{ $user->name }}</h2>
                <p>จัดการข้อมูลส่วนตัว คำนวณภาษี และดูประวัติของคุณได้จากหน้านี้</p>
            </div>
            <div class="profile-badge">{{ mb_substr($user->name, 0, 1) }}</div>
        </section>
        <section class="profile-card">
            <h3>ข้อมูลบัญชี</h3>
            <div class="profile-details">
                <p><span>ชื่อผู้ใช้</span><strong>{{ $user->name }}</strong></p>
                <p><span>อีเมล</span><strong>{{ $user->email }}</strong></p>
                <p><span>ระดับผู้ใช้</span><strong>{{ $user->role === 'admin' ? 'Admin (ผู้ดูแลระบบ)' : 'User (สมาชิก)' }}</strong></p>
            </div>
        </section>
        <section class="dashboard-grid">
            <a class="dashboard-action primary" href="{{ route('tax.show') }}"><span>🧮</span><div><h3>คำนวณภาษี</h3><p>กรอกข้อมูลรายได้และค่าลดหย่อน</p></div></a>
            <a class="dashboard-action" href="{{ route('profile.edit') }}"><span>👤</span><div><h3>แก้ไขข้อมูลส่วนตัว</h3><p>เปลี่ยนชื่อ อีเมล หรือรหัสผ่าน</p></div></a>
            <a class="dashboard-action" href="{{ route('tax.records.index') }}"><span>📋</span><div><h3>ประวัติผลคำนวณ</h3><p>ดูและจัดการผลที่บันทึกไว้</p></div></a>
            @if($user->role === 'admin')
                <a class="dashboard-action admin" href="{{ route('admin.dashboard') }}"><span>⚙️</span><div><h3>จัดการระบบ</h3><p>ไปยังส่วนผู้ดูแลระบบ</p></div></a>
            @endif
        </section>
    </main>
</body>
</html>
