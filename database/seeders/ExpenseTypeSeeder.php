<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use App\Models\IncomeType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        // income code, expense code, name, method, rate, cap, limit group, description
        $items = [
            ['40_1', 'salary_flat', 'เงินเดือนหักเหมา', 'flat', 0.5, 100000, 'salary_commission', 'ม.40(1) และ (2) ใช้เพดาน 100,000 บาทร่วมกัน ต้องรวมก่อนคำนวณ'],
            ['40_2', 'commission_flat', 'รับทำงานให้หักเหมา', 'flat', 0.5, 100000, 'salary_commission', 'ม.40(1) และ (2) ใช้เพดาน 100,000 บาทร่วมกัน ไม่ใช่ประเภทละ 100,000 บาท'],
            ['40_3', 'rights_flat', 'สิทธิและลิขสิทธิ์หักเหมา', 'flat', 0.5, 100000, null, 'เฉพาะค่าแห่งกู๊ดวิลล์ ลิขสิทธิ์ และสิทธิอื่น'],
            ['40_3', 'rights_actual', 'สิทธิและลิขสิทธิ์ตามจริง', 'actual', null, null, null, 'ค่าใช้จ่ายจำเป็นและสมควร มีหลักฐาน; ฟอร์มปัจจุบันยังไม่รองรับ'],
            ['40_5', 'building_flat', 'บ้าน อาคาร แพ', 'flat', 0.3, null, null, 'หักเหมา 30%'],
            ['40_5', 'agriculture_land_flat', 'ที่ดินเกษตร', 'flat', 0.2, null, null, 'หักเหมา 20%'],
            ['40_5', 'other_land_flat', 'ที่ดินอื่น', 'flat', 0.15, null, null, 'หักเหมา 15%'],
            ['40_5', 'vehicle_flat', 'ยานพาหนะ', 'flat', 0.3, null, null, 'หักเหมา 30%'],
            ['40_5', 'other_property_flat', 'ทรัพย์สินอื่น', 'flat', 0.1, null, null, 'หักเหมา 10%'],
            ['40_5', 'rental_actual', 'ค่าเช่าตามจริง', 'actual', null, null, null, 'ค่าใช้จ่ายจำเป็นและสมควร มีหลักฐาน'],
            ['40_6', 'medical_flat', 'ประกอบโรคศิลปะ', 'flat', 0.6, null, null, 'หักเหมา 60%'],
            ['40_6', 'profession_other_flat', 'วิชาชีพอิสระอื่นที่กำหนด', 'flat', 0.3, null, null, 'กฎหมาย บัญชี วิศวกรรม สถาปัตยกรรม ประณีตศิลปกรรม'],
            ['40_6', 'profession_actual', 'วิชาชีพอิสระตามจริง', 'actual', null, null, null, 'ค่าใช้จ่ายจำเป็นและสมควร มีหลักฐาน; ฟอร์มปัจจุบันยังไม่รองรับ'],
            ['40_7', 'construction_flat', 'รับเหมาก่อสร้างหักเหมา', 'flat', 0.6, null, null, 'ต้องจัดหาสัมภาระสำคัญนอกจากเครื่องมือ'],
            ['40_7', 'construction_actual', 'รับเหมาก่อสร้างตามจริง', 'actual', null, null, null, 'ค่าใช้จ่ายจำเป็นและสมควร มีหลักฐาน; ฟอร์มปัจจุบันยังไม่รองรับ'],
            ['40_8', 'resale_flat', 'ซื้อสินค้ามาขาย', 'flat', 0.6, null, null, 'ผู้ขายไม่ได้เป็นผู้ผลิต และกิจการตรงตามเกณฑ์'],
            ['40_8', 'restaurant_flat', 'ปรุงอาหารหรือเครื่องดื่มจำหน่าย', 'flat', 0.6, null, null, 'เฉพาะกิจการตรงตามเกณฑ์'],
            ['40_8', 'business_actual', 'ธุรกิจทั่วไปตามจริง', 'actual', null, null, null, 'มีหลักฐานค่าใช้จ่ายจำเป็นและสมควร ไม่รวมเงินได้ที่มีกฎคำนวณเฉพาะ'],
        ];

        foreach ([2568, 2569] as $year) {
            foreach ($items as $item) {
                $incomeType = IncomeType::where('code', $item[0])->firstOrFail();
                $expenseType = ExpenseType::where('tax_year', $year)
                    ->where('code', $item[1])->first();
                if ($expenseType === null) {
                    $expenseType = new ExpenseType();
                    $expenseType->income_type_id = $incomeType->id;
                    $expenseType->code = $item[1];
                    $expenseType->name = $item[2];
                    $expenseType->tax_year = $year;
                    $expenseType->method = $item[3];
                    $expenseType->rate = $item[4];
                    $expenseType->max_amount = $item[5];
                    $expenseType->limit_group = $item[6];
                    $expenseType->description = $item[7];
                    $expenseType->is_active = true;
                    $expenseType->save();
                }
            }
        }
    }
}
