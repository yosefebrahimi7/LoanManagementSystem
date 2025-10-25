<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'default_interest_rate',
                'value' => '14.5',
                'type' => 'decimal',
                'group' => 'loan',
                'description' => 'Default annual interest rate for loans',
            ],
            [
                'key' => 'penalty_rate_per_day',
                'value' => '0.5',
                'type' => 'decimal',
                'group' => 'loan',
                'description' => 'Penalty rate percentage per day for late payments',
            ],
            [
                'key' => 'min_loan_amount',
                'value' => '1000000',
                'type' => 'integer',
                'group' => 'loan',
                'description' => 'Minimum loan amount in smallest currency unit',
            ],
            [
                'key' => 'max_loan_amount',
                'value' => '100000000',
                'type' => 'integer',
                'group' => 'loan',
                'description' => 'Maximum loan amount in smallest currency unit',
            ],
            [
                'key' => 'min_loan_term_months',
                'value' => '3',
                'type' => 'integer',
                'group' => 'loan',
                'description' => 'Minimum loan term in months',
            ],
            [
                'key' => 'max_loan_term_months',
                'value' => '36',
                'type' => 'integer',
                'group' => 'loan',
                'description' => 'Maximum loan term in months',
            ],
            [
                'key' => 'zarinpal_merchant_id',
                'value' => '',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Zarinpal merchant ID for sandbox',
            ],
            [
                'key' => 'zarinpal_sandbox_mode',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Enable Zarinpal sandbox mode',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
