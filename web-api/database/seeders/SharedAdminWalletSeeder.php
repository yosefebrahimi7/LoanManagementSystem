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
        // Balance is in Rials (100,000,000 Rials = 10,000,000 Tomans initial capital)
        $existingWallet = Wallet::updateOrCreate(
            [
                'is_shared' => true,
                'user_id' => null,
            ],
            [
                'balance' => 100000000, // 100M Rials = 10M Tomans initial capital
                'currency' => 'IRR',
            ]
        );

        if ($existingWallet->wasRecentlyCreated) {
            $this->command->info('Shared admin wallet created with initial balance of 10,000,000 Tomans.');
        } else {
            $this->command->info('Shared admin wallet already exists. Balance: ' . number_format($existingWallet->balance / 100, 0) . ' Tomans');
        }
    }
}
