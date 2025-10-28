<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class SharedAdminWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update shared admin wallet with initial balance
        // Balance is in Rials (100,000,000,000 Rials = 10,000,000,000 Tomans initial capital)
        $existingWallet = Wallet::updateOrCreate(
            [
                'is_shared' => true,
                'user_id' => null,
            ],
            [
                'balance' => 100000000000, // 100B Rials = 10B Tomans initial capital
                'currency' => 'IRR',
            ]
        );

        if ($existingWallet->wasRecentlyCreated) {
            $this->command->info('Shared admin wallet created with initial balance of 10,000,000,000 Tomans.');
        } else {
            $this->command->info('Shared admin wallet already exists. Balance: ' . number_format($existingWallet->balance / 10, 0) . ' Tomans');
        }
    }
}
