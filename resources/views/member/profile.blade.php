<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลส่วนตัว - Personal Tax</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
<body class="profile-page">
    <header>
        <h1>Personal Tax</h1>
        <nav>
            <a href="{{ route('member.index') }}">ส่วนสมาชิก</a>
            <a href="{{ route('tax.records.index') }}">ประวัติผลคำนวณ</a>
            <a href="{{ route('tax.show') }}">คำนวณภาษี</a>
            @if($user->role === 'admin')
                <a href="{{ route('admin.dashboard') }}">จัดการระบบ</a>
            @endif
        </nav>
    </header>

    <main class="profile-container">
        <a href="{{ $user->role === 'admin' ? route('admin.dashboard') : route('member.index') }}" class="back-link">← กลับหน้าแดชบอร์ด</a>

        <section class="profile-hero">
            <div class="profile-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
            <div class="profile-heading">
                <p class="profile-eyebrow">ข้อมูลบัญชี</p>
                <h2>แก้ไขข้อมูลส่วนตัว</h2>
                <p>ปรับปรุงชื่อ อีเมล และรหัสผ่านที่ใช้เข้าสู่ระบบ</p>
            </div>
            <span class="role-badge {{ $user->role === 'admin' ? 'role-admin' : '' }}">
                {{ $user->role === 'admin' ? 'Admin · ผู้ดูแลระบบ' : 'User · สมาชิก' }}
            </span>
        </section>

        @if(session('success'))
            <div class="profile-alert success" role="status">
                <strong>บันทึกสำเร็จ</strong>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="profile-alert error" role="alert">
                <strong>กรุณาตรวจสอบข้อมูล</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="profile-layout">
            <aside class="account-card">
                <h3>บัญชีของฉัน</h3>
                <dl>
                    <div>
                        <dt>ชื่อปัจจุบัน</dt>
                        <dd>{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt>อีเมลปัจจุบัน</dt>
                        <dd>{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt>ระดับผู้ใช้</dt>
                        <dd>{{ $user->role === 'admin' ? 'ผู้ดูแลระบบ' : 'สมาชิก' }}</dd>
                    </div>
                </dl>
                <p class="account-note">อีเมลนี้ใช้สำหรับเข้าสู่ระบบ กรุณาตรวจสอบให้ถูกต้องก่อนบันทึก</p>
            </aside>

            <section class="edit-card">
                <div class="card-heading">
                    <div>
                        <p class="step-label">ข้อมูลพื้นฐาน</p>
                        <h3>รายละเอียดส่วนตัว</h3>
                    </div>
                    <span>ช่องที่มี * จำเป็นต้องกรอก</span>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="form-field">
                        <label for="name">ชื่อผู้ใช้ <span>*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" maxlength="255" required autocomplete="name" class="{{ $errors->has('name') ? 'input-error' : '' }}">
                        <small>ชื่อที่แสดงในหน้าแดชบอร์ดและประวัติของคุณ</small>
                    </div>

                    <div class="form-field">
                        <label for="email">อีเมล <span>*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" maxlength="255" required autocomplete="username" class="{{ $errors->has('email') ? 'input-error' : '' }}">
                        <small>ใช้เป็นชื่อบัญชีสำหรับเข้าสู่ระบบ</small>
                    </div>

                    <div class="password-heading">
                        <p class="step-label">ความปลอดภัย</p>
                        <h3>เปลี่ยนรหัสผ่าน</h3>
                        <p>หากไม่ต้องการเปลี่ยนรหัสผ่าน ให้เว้นสองช่องนี้ว่างไว้</p>
                    </div>

                    <div class="password-grid">
                        <div class="form-field">
                            <label for="password">รหัสผ่านใหม่</label>
                            <input type="password" name="password" id="password" minlength="8" maxlength="255" autocomplete="new-password" class="{{ $errors->has('password') ? 'input-error' : '' }}">
                            <small>อย่างน้อย 8 ตัวอักษร</small>
                        </div>

                        <div class="form-field">
                            <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password">
                            <small>กรอกให้ตรงกับรหัสผ่านใหม่</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ $user->role === 'admin' ? route('admin.dashboard') : route('member.index') }}" class="cancel-button">ยกเลิก</a>
                        <button type="submit" class="save-button">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
