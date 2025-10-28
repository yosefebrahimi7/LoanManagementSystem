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
        // Check if shared admin wallet already exists
        $existingWallet = Wallet::where('is_shared', true)
            ->whereNull('user_id')
            ->first();

        if (!$existingWallet) {
            Wallet::create([
                'user_id' => null,
                'balance' => 0,
                'currency' => 'IRR',
                'is_shared' => true,
            ]);

            $this->command->info('Shared admin wallet created successfully.');
        } else {
            $this->command->info('Shared admin wallet already exists.');
        }
    }
}
