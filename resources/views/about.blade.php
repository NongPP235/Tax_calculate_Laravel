<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
</head>
<body>
    <header>
        <h1>Personal Tax</h1>
        <nav>
            <a href="/">หน้าหลัก</a>
            <a href="/knowledge">ความรู้เกี่ยวกับภาษี</a>
            <a href="/about">เกี่ยวกับ</a>
            
            <!-- ส่วนสลับสิทธิ์การเข้าสู่ระบบ -->
            @guest
                <a href="{{ route('login') }}">เข้าสู่ระบบ</a>
                <a href="{{ route('register') }}">สมัครสมาชิก</a>
            @endguest
            @auth
                <a href="{{ route('member.index') }}">ส่วนสมาชิก</a>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">จัดการระบบ (Admin)</a>
                @endif
                <span>👤 {{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">ออกจากระบบ</button>
                </form>
            @endauth
        </nav>
    </header>

    <main class="about-container">

        <section>
            <h2>วัตถุประสงค์ในการทำ</h2>
            <p>งานนี้นี้มีวัตถุประสงค์เพื่อพัฒนาเว็บแอปพลิเคชันสำหรับคำนวณภาษีเงินได้บุคคลธรรมดาเพื่ออำนวยความสะดวกแก่ผู้ที่มีรายได้ในการคำนวณภาษี
                โดยสามารถกรอกข้อมูลรายได้ ค่าใช้จ่าย และค่าลดหย่อนเพื่อให้ระบบคำนวณภาษีที่ต้องชำระได้อย่างรวดเร็วและถูกต้องพร้อมทั้งช่วยลดความสับสนในการคำนวณและส่งเสริมให้ผู้ใช้งานมีความรู้ความเข้าใจเกี่ยวกับภาษี สามารถเตรียมตัวและยื่นภาษีได้อย่างถูกต้องตามกฎหมาย</p>
        </section>
        
        <hr>
        <section>
            <h2>ขอบเขตด้านการคำนวณภาษี</h2>

            <p>ระบบสามารถคำนวณภาษีเงินได้บุคคลธรรมดาเบื้องต้น โดยมีขั้นตอนการคำนวณ ดังนี้</p>

            <ol>
                <li>คำนวณรายได้รวมของผู้ใช้งาน</li>
                <li>หักค่าใช้จ่ายตามข้อมูลที่กำหนด</li>
                <li>คำนวณรายได้หลังหักค่าใช้จ่าย</li>
                <li>หักค่าลดหย่อนภาษีตามสิทธิ์ของผู้ใช้งาน</li>
                <li>คำนวณเงินได้สุทธิ</li>
                <li>คำนวณจำนวนภาษีตามอัตราภาษีเงินได้บุคคลธรรมดาแบบขั้นบันได</li>
                <li>แสดงผลจำนวนภาษีที่ผู้ใช้งานต้องชำระโดยประมาณ</li>
            </ol>
        </section>

        <hr>

        <section class="about-team">
            <h2>ผู้จัดทำ</h2>
            <p>นาย พีรพัฒน์ บรรเทิงศักดิ์ศิริ รหัสนักศึกษา 673380633-5 section 3</p>
            <p>นาย ป๋านัท รหัสนักศึกษา 99999999</p>
        </section>
        
    </main>
    <footer>
        <p>&copy; {{ date('Y') }} PersonalTaxCalculator. All rights reserved.</p>
    </footer>
</body>
</html>
