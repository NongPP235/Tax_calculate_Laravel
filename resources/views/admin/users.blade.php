<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสิทธิผู้ใช้ - Personal Tax</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
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
        <h2>จัดการผู้ใช้</h2>
    <p>ผู้ดูแลแก้ไขชื่อ อีเมล เปลี่ยนสิทธิ หรือลบบัญชีอื่นได้</p>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>ข้อมูลผู้ใช้</th><th>สิทธิ</th><th>จัดการ</th></tr></thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-user-form">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $user->name }}" required aria-label="ชื่อผู้ใช้">
                                <input type="email" name="email" value="{{ $user->email }}" required aria-label="อีเมลผู้ใช้">
                                <button type="submit">บันทึกข้อมูล</button>
                            </form>
                        </td>
                        <td>{{ $user->role }}</td>
                        <td>
                            @if($user->id === Auth::id())
                                บัญชีของคุณ
                            @else
                                <form action="{{ route('admin.users.role', $user) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <label for="role-{{ $user->id }}">สิทธิของ {{ $user->name }}</label>
                                    <select name="role" id="role-{{ $user->id }}">
                                        <option value="user" @selected($user->role === 'user')>User</option>
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    </select>
                                    <button type="submit">บันทึกสิทธิ</button>
                                </form>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('ต้องการลบบัญชี {{ $user->email }} หรือไม่')">ลบบัญชี</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <nav aria-label="หน้ารายชื่อผู้ใช้">
        @if($users->previousPageUrl())
            <a href="{{ $users->previousPageUrl() }}">หน้าก่อนหน้า</a>
        @endif
        <span>หน้า {{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
        @if($users->nextPageUrl())
            <a href="{{ $users->nextPageUrl() }}">หน้าถัดไป</a>
        @endif
    </nav>
    </main>
</body>
</html>
