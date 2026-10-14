<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMember
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() === null) {
            return redirect()->route('login');
        }

        $role = $request->user()->role;
        if ($role !== 'user' && $role !== 'admin') {
            abort(403, 'บัญชีนี้ไม่มีสิทธิเข้าถึงส่วนสมาชิก');
        }

        return $next($request);
    }
}
