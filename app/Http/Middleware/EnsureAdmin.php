<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // ตรวจที่เซิร์ฟเวอร์ แม้ผู้ใช้จะพิมพ์ URL หน้า Admin เอง
        if ($request->user() === null) {
            return redirect()->route('login');
        }

        if ($request->user()->role !== 'admin') {
            abort(403, 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น');
        }

        return $next($request);
    }
}
