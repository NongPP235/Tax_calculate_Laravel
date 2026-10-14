<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="auth-page">
    <h1>เข้าสู่ระบบ</h1>
    <p class="auth-subtitle">เข้าสู่ระบบเพื่อดูประวัติการคำนวณภาษี</p>

    <main>
        <form action="{{ route('login.auth') }}" method="post">
            @csrf

            @if (session('status'))
                <div class="auth-status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="auth-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section>
                <label for="email">อีเมล</label>
                <input type="email" name="email" id="email"
                       value="{{ old('email') }}"
                       placeholder="Enter Email Address"
                       class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                       required autofocus autocomplete="username">
            </section>

            <section>
                <label for="password">รหัสผ่าน</label>
                <input type="password" name="password" id="password"
                       placeholder="Enter Password"
                       class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                       required autocomplete="current-password">
            </section>

            <div class="form-options">
                <label for="remember" class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    จดจำฉันไว้
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">ลืมรหัสผ่าน?</a>
                @endif
            </div>

            <button type="submit">เข้าสู่ระบบ</button>

            <p class="auth-link">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก</a></p>
        </form>
    </main>

    <a href="/" class="back-home">← กลับหน้าหลัก</a>
</body>
</html>
