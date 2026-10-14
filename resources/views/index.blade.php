<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>index</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body>
    
    <header>
        <h1>Personal Tax Calculate</h1>
        <nav>
            <a href="/">หน้าหลัก</a>
            <a href="/knowledge">ความรู้เกี่ยวกับภาษี</a>
            <a href="/about">เกี่ยวกับ</a>
            
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

    <main>
        @if(session('success'))
            <p role="status"><strong>{{ session('success') }}</strong></p>
        @endif

        <!-- 2. HERO SECTION (จุดดึงสายตาหลักและปุ่มกดใช้งาน) -->
        <section class="hero">
            <div class="hero-content">
                <h1>วางแผนและคำนวณภาษีให้เป็นเรื่องง่าย</h1>
                <p>เครื่องมือคำนวณภาษีเงินได้บุคคลธรรมดาออนไลน์ ถูกต้อง แม่นยำ</p>
                <div class="bta-actions">
                    <a href="{{ route('tax.show') }}" class ="btn-primary">เริ่มคำนวณภาษีฟรี</a>
                </div>
            </div>
            <div class="hero-image">
                <!-- [พื้นที่สำหรับใส่รูปภาพประกอบ หรือ ไอคอนเครื่องคิดเลข/การเงิน] -->
            </div>
        </section>

        <!-- 3. FEATURES SECTION (จุดเด่นของระบบ 3 บล็อก) -->
        <section class="features">
            <h2>ทำไมต้องใช้เครื่องมือของเรา?</h2>
            <div class="feature-grid">
                <article class="feature-item">
                    <h3>คำนวณไว</h3>
                    <p>กรอกข้อมูลเสร็จ รู้ผลลัพธ์สุทธิและภาษีทันที</p>
                </article>
                <article class="feature-item">
                    <h3>รองรับทุกรายได้</h3>
                    <p>แยกคำนวณรายได้มาตรา 40(1) - 40(8) ตามกฎหมายสัดส่วนสรรพากร</p>
                </article>
                <article class="feature-item">
                    <h3>บันทึกประวัติ</h3>
                    <p>ระบบสมาชิกช่วยเซฟข้อมูลภาษีเพื่อเปรียบเทียบในแต่ละปี</p>
                </article>
            </div>
        </section>

        <!-- 5. CALL TO ACTION FOR GUESTS (กล่องชวนสมัครสมาชิกท้ายหน้า) -->
        @guest
            <section class="cta-register">
                <h2>อยากบันทึกข้อมูลไว้เปรียบเทียบปีหน้าไหม?</h2>
                <p>สมัครสมาชิกกับเราเพื่อเก็บข้อมูลประวัติการคำนวณภาษีของคุณย้อนหลังได้ฟรี</p>
                <a href="{{ route('register') }}" class="btn-secondary">สมัครสมาชิกตอนนี้</a>
            </section>
        @endguest
    </main>

    <!-- 6. FOOTER (ส่วนท้ายเว็บ) -->
    <footer>
        <p>&copy; {{ date('Y') }} TaxCalculator. All rights reserved.</p>
    </footer>

</body>
</html>
