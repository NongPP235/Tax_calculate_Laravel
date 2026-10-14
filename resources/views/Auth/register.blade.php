<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body class="auth-page">

    <h1>สมัครสมาชิก</h1>

    <main>
        <form action="{{route('register.store') }}" method="post">
            @csrf
            @if ($errors->any())
                <div class="auth-alert" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <section>
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Enter Username" required maxlength="255" autocomplete="name">
            </section>

            <section>
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Enter Email Address" required maxlength="255" autocomplete="username">
            </section>

            <section>
                <label for="password">Create Password</label>
                <input type="password" name="password" id="password" placeholder="Enter Password" required minlength="8" maxlength="255" autocomplete="new-password">
            </section>

            <section>
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" required minlength="8" maxlength="255" autocomplete="new-password">
            </section>
    
            <button type="submit">สมัครสมาชิก</button>
            <p class="auth-link">มีบัญชีแล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ</a></p>
        </form>
    </main>

    <a href="/" class="back-home">← กลับหน้าหลัก</a>
</body>
</html>
