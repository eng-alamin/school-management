<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PricingRate;

class PricingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            ['type' => 'student',     'label' => 'Per Active Student Monthly Fee', 'rate' => 1.00, 'is_active' => true],
            ['type' => 'sms',         'label' => 'SMS Charge',                     'rate' => 0.50, 'is_active' => false],
            ['type' => 'attendance',  'label' => 'Attendance Entry',               'rate' => 0.10, 'is_active' => false],
        ];

        foreach ($rates as $rate) {
            PricingRate::updateOrCreate(['type' => $rate['type']], $rate);
        }
    }
}
