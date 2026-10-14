<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // สร้างประเภทเงินได้ก่อน เพราะค่าใช้จ่ายต้องอ้างอิง income_type_id
        // ไม่สร้างบัญชีผู้ใช้หรือ Admin ที่มีรหัสผ่านเริ่มต้น
        $this->call([
            IncomeTypeSeeder::class,
            ExpenseTypeSeeder::class,
            DeductionTypeSeeder::class,
            TaxBracketSeeder::class,
        ]);
    }
}
