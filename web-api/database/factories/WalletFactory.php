<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => $this->faker->numberBetween(0, 10000000), // 0 to 10M Rials (0 to 1M Tomans)
            'currency' => 'IRR',
            'is_shared' => false,
        ];
    }

    /**
     * Indicate that the wallet has zero balance.
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0,
        ]);
    }

    /**
     * Indicate that the wallet has a specific balance.
     */
    public function withBalance(int $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
        ]);
    }

    /**
     * Indicate that the wallet has a high balance.
     */
    public function wealthy(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $this->faker->numberBetween(5000000, 50000000), // 5M to 50M Rials (500K to 5M Tomans)
        ]);
    }

    /**
     * Indicate that the wallet is a shared admin wallet.
     */
    public function sharedAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'balance' => 100000000, // 100M Rials = 10M Tomans
            'is_shared' => true,
        ]);
    }
}
