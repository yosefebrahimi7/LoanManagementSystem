<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'type' => $this->faker->randomElement([
                WalletTransaction::TYPE_CREDIT,
                WalletTransaction::TYPE_DEBIT,
            ]),
            'amount' => $this->faker->numberBetween(100000, 1000000), // 1,000 to 10,000 Toman
            'balance_after' => $this->faker->numberBetween(0, 5000000),
            'description' => $this->faker->sentence(),
            'meta' => [
                'reference_id' => $this->faker->uuid(),
                'source' => $this->faker->word(),
            ],
        ];
    }

    /**
     * Indicate that the transaction is a credit.
     */
    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletTransaction::TYPE_CREDIT,
        ]);
    }

    /**
     * Indicate that the transaction is a debit.
     */
    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletTransaction::TYPE_DEBIT,
        ]);
    }
}

