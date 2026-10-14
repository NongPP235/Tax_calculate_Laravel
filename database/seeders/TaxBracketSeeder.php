<?php

namespace Database\Seeders;

use App\Models\TaxBracket;
use Illuminate\Database\Seeder;

class TaxBracketSeeder extends Seeder
{
    public function run(): void
    {
        // lower bound, upper bound, rate; 150,000 บาทแรกได้รับยกเว้น
        $items = [
            [0, 150000, 0],
            [150000, 300000, 0.05],
            [300000, 500000, 0.1],
            [500000, 750000, 0.15],
            [750000, 1000000, 0.2],
            [1000000, 2000000, 0.25],
            [2000000, 5000000, 0.3],
            [5000000, null, 0.35],
        ];

        foreach ([2568, 2569] as $year) {
            foreach ($items as $item) {
                $bracket = TaxBracket::where('tax_year', $year)
                    ->where('lower_bound', $item[0])->first();
                if ($bracket === null) {
                    $bracket = new TaxBracket();
                    $bracket->tax_year = $year;
                    $bracket->lower_bound = $item[0];
                    $bracket->upper_bound = $item[1];
                    $bracket->rate = $item[2];
                    $bracket->save();
                }
            }
        }
    }
}
