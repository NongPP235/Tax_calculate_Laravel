<?php

namespace Database\Seeders;

use App\Models\DeductionType;
use Illuminate\Database\Seeder;

class DeductionTypeSeeder extends Seeder
{
    public function run(): void
    {
        // code, name, cap, description (บางรายการ cap เป็นเพดานต่อคน)
        $items = [
            ['personal', 'ลดหย่อนส่วนตัว', 60000, 'สิทธิพื้นฐานของผู้มีเงินได้'],
            ['spouse', 'คู่สมรสไม่มีเงินได้', 60000, 'ต้องจดทะเบียนสมรสและเข้าเงื่อนไขตามกฎหมาย'],
            ['child', 'บุตรชอบด้วยกฎหมาย', 30000, 'เพดานต่อคน ไม่ใช่ยอดรวม ต้องตรวจอายุ การศึกษา รายได้ และความเป็นบุตรตามเกณฑ์'],
            ['child_bonus', 'สิทธิบุตรเพิ่ม', 30000, 'เพิ่มต่อคน สำหรับบุตรชอบด้วยกฎหมายคนที่สองเป็นต้นไปที่เกิดตั้งแต่ปี 2561'],
            ['adopted_child', 'บุตรบุญธรรม', 30000, 'เพดานต่อคน ต้องจดทะเบียนและตรวจจำนวนรวมที่ใช้สิทธิกับบุตรชอบด้วยกฎหมาย'],
            ['disabled_care', 'อุปการะคนพิการหรือทุพพลภาพ', 60000, 'เพดานต่อคน ต้องตรวจหลักฐาน รายได้ และการใช้สิทธิซ้ำ; บุคคลอื่นได้ไม่เกิน 1 คน'],
            ['life_insurance', 'ประกันชีวิต', 100000, 'เฉพาะเบี้ยประกันที่เข้าเงื่อนไข รวมประกันสุขภาพตนเองไม่เกิน 100,000 บาท'],
            ['health_insurance', 'ประกันสุขภาพตนเอง', 25000, 'รวมกับประกันชีวิตไม่เกิน 100,000 บาท'],
            ['donation', 'บริจาคทั่วไป', null, 'หักตามจริงไม่เกิน 10% ของเงินได้หลังหักค่าใช้จ่ายและค่าลดหย่อนอื่น ต้องคำนวณฐานใน Controller'],
        ];

        foreach ([2568, 2569] as $year) {
            $socialCap = 9000;
            if ($year === 2569) {
                $socialCap = 10500;
            }
            $yearItems = $items;
            $yearItems[] = ['social_security', 'ประกันสังคม', $socialCap,
                'จ่ายจริงตามสิทธิ; เพดานนี้อ้างอิง ม.33 ส่งเต็มปี ประเภทอื่นต้องตรวจสิทธิตามจริง'];

            foreach ($yearItems as $item) {
                $deductionType = DeductionType::where('tax_year', $year)
                    ->where('code', $item[0])->first();
                if ($deductionType === null) {
                    $deductionType = new DeductionType();
                    $deductionType->code = $item[0];
                    $deductionType->name = $item[1];
                    $deductionType->tax_year = $year;
                    $deductionType->max_amount = $item[2];
                    $deductionType->description = $item[3];
                    $deductionType->is_active = true;
                    $deductionType->save();
                }
            }
        }
        // ยกเว้นเงินได้คนพิการ/ผู้สูงอายุเก็บใน TaxRecord ไม่ใช่ค่าลดหย่อนทั่วไป
    }
}
