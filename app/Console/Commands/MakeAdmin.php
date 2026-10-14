<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MakeAdmin extends Command
{
    protected $signature = 'user:make-admin {email : อีเมลบัญชีที่สมัครไว้แล้ว}';

    protected $description = 'กำหนดบัญชีที่มีอยู่ให้เป็น Admin';

    public function handle()
    {
        if (! Schema::hasColumn('users', 'role')) {
            $this->error('กรุณารัน php artisan migrate ก่อน');
            return 1;
        }

        $email = strtolower(trim($this->argument('email')));
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error('ไม่พบบัญชีนี้ กรุณาสมัครสมาชิกก่อน');
            return 1;
        }

        $user->role = 'admin';
        $user->save();
        $this->info('กำหนด '.$email.' เป็น Admin เรียบร้อยแล้ว');

        return 0;
    }
}
