<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Personal Tax</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/member.css') }}">
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
    <main class="member-panel">
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
        @yield('content')
    </main>
</body>
</html>
