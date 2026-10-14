<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{

    // แสดงฟอร์ม เหมือนตัวอย่าง create() ในบทเรียน
    public function login(): View
    {
        return view('Auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {

        // ทำให้อีเมลตัวใหญ่และตัวเล็กใช้เข้าสู่ระบบได้เหมือนกัน
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        // ตรวจข้อมูล แล้วเก็บเฉพาะค่าที่ผ่านกฎไว้ใน $validated
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'in:on,1,0'],
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.max' => 'อีเมลต้องไม่เกิน 255 ตัวอักษร',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.string' => 'รหัสผ่านต้องเป็นข้อความ',
            'password.max' => 'รหัสผ่านต้องไม่เกิน 255 ตัวอักษร',
        ]);

        // จดจำฉันไม่ใช่ข้อมูลที่จะใช้ค้นหาผู้ใช้ในฐานข้อมูล
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];
        $remember = $request->boolean('remember');

        // อีเมลและ IP เดิม ลองรหัสผ่านผิดได้ 5 ครั้งใน 1 นาที
        // RateLimiter เป็นตัวช่วยของ Laravel นอกเหนือจากตัวอย่างในสไลด์
        $loginKey = 'login:'.$validated['email'].'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($loginKey, 5)) {
            $seconds = RateLimiter::availableIn($loginKey);
            return back()->withErrors([
                'email' => 'ลองเข้าสู่ระบบหลายครั้งเกินไป กรุณารอ '.$seconds.' วินาที',
            ])->onlyInput('email');
        }

        // ถ้าบัญชีเปิดยืนยันสองขั้นตอนไว้ ต้องยืนยันก่อนเข้าสู่ระบบ
        $user = User::where('email', $validated['email'])->first();
        if ($user && $user->two_factor_secret && $user->two_factor_confirmed_at
            && Hash::check($validated['password'], $user->password)) {
            RateLimiter::clear($loginKey);
            $request->session()->regenerate();
            $request->session()->put('login.id', $user->id);
            $request->session()->put('login.remember', $remember);

            return redirect()->route('two-factor.login');
        }

        // attempt ตรวจอีเมลและรหัสผ่าน ถ้าถูกต้องจะเข้าสู่ระบบให้
        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($loginKey);
            $request->session()->forget(['login.id', 'login.remember']);
            $request->session()->regenerate();

            // กลับหน้าที่ต้องการเข้าก่อนหน้า ถ้าไม่มีให้กลับหน้าหลัก
            return redirect()->intended(route('home'))
                ->with('success', 'เข้าสู่ระบบเรียบร้อยแล้ว');
        }

        RateLimiter::hit($loginKey, 60);
        $request->session()->forget(['login.id', 'login.remember']);

        return back()->withErrors([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('email');
    }

    public function register(): View
    {
        return view('Auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        // confirmed จะตรวจว่าตรงกับช่อง password_confirmation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ',
            'name.string' => 'ชื่อต้องเป็นข้อความ',
            'name.max' => 'ชื่อต้องไม่เกิน 255 ตัวอักษร',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.max' => 'อีเมลต้องไม่เกิน 255 ตัวอักษร',
            'email.unique' => 'อีเมลนี้มีผู้ใช้งานแล้ว',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.string' => 'รหัสผ่านต้องเป็นข้อความ',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.max' => 'รหัสผ่านต้องไม่เกิน 255 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        // เหมือน new Product() ในบทเรียน: กำหนดค่าทีละช่องแล้ว save()
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        // ผู้สมัครใหม่เป็น User เสมอ ไม่รับ role จากฟอร์ม
        $user->role = 'user';
        // รหัสผ่านต้องเข้ารหัสก่อนบันทึก ไม่เก็บเป็นข้อความธรรมดา
        $user->password = Hash::make($validated['password']);
        $user->save();

        Auth::login($user);
        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();

        // with() เก็บข้อความชั่วคราวใน Flash Session ตามบทเรียน Part 03
        return redirect()->route('home')
            ->with('success', 'สมัครสมาชิกเรียบร้อยแล้ว');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // ล้างข้อมูลใน session และเปลี่ยน CSRF token หลังออกจากระบบ
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'ออกจากระบบเรียบร้อยแล้ว');
    }
}
