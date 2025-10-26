<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amounts = [1000000, 2000000, 5000000, 10000000, 20000000, 50000000]; // 1M to 50M IRR
        $terms = [6, 12, 18, 24, 36]; // 6 to 36 months
        $interestRates = [12.0, 14.5, 16.0, 18.0, 20.0]; // 12% to 20%
        
        $amount = $this->faker->randomElement($amounts);
        $termMonths = $this->faker->randomElement($terms);
        $interestRate = $this->faker->randomElement($interestRates);
        
        // Calculate monthly payment using PMT formula
        $monthlyRate = $interestRate / 100 / 12;
        $monthlyPayment = $amount * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / 
                         (pow(1 + $monthlyRate, $termMonths) - 1);
        
        $statuses = [
            Loan::STATUS_PENDING,
            Loan::STATUS_APPROVED,
            Loan::STATUS_REJECTED,
            Loan::STATUS_ACTIVE,
            Loan::STATUS_DELINQUENT,
            Loan::STATUS_PAID
        ];
        
        $status = $this->faker->randomElement($statuses);
        
        return [
            'user_id' => User::factory(),
            'amount' => $amount,
            'term_months' => $termMonths,
            'interest_rate' => $interestRate,
            'monthly_payment' => intval(round($monthlyPayment)),
            'remaining_balance' => $amount,
            'status' => $status,
            'start_date' => $this->faker->dateTimeBetween('-1 year', '+1 month'),
            'approved_at' => $status !== Loan::STATUS_PENDING ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'approved_by' => $status !== Loan::STATUS_PENDING ? User::factory() : null,
            'rejection_reason' => $status === Loan::STATUS_REJECTED ? $this->faker->sentence() : null,
        ];
    }

    /**
     * Indicate that the loan is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Loan::STATUS_PENDING,
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the loan is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Loan::STATUS_APPROVED,
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'approved_by' => User::factory(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the loan is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Loan::STATUS_REJECTED,
            'approved_at' => null,
            'approved_by' => User::factory(),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the loan is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Loan::STATUS_ACTIVE,
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'approved_by' => User::factory(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the loan is delinquent.
     */
    public function delinquent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Loan::STATUS_DELINQUENT,
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'approved_by' => User::factory(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the loan is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Loan::STATUS_PAID,
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'approved_by' => User::factory(),
            'remaining_balance' => 0,
            'rejection_reason' => null,
        ]);
    }
}
