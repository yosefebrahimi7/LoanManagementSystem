<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanSchedule>
 */
class LoanScheduleFactory extends Factory
{
    protected $model = LoanSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loan = Loan::factory()->create();
        $monthlyPayment = $loan->monthly_payment;
        $installmentNumber = $this->faker->numberBetween(1, $loan->term_months);
        
        // Calculate principal and interest portions
        $remainingBalance = $loan->amount - (($installmentNumber - 1) * $monthlyPayment);
        $interestRate = $loan->interest_rate / 100 / 12;
        $interestAmount = intval(round($remainingBalance * $interestRate));
        $principalAmount = $monthlyPayment - $interestAmount;
        
        // For the last payment, adjust to remaining balance
        if ($installmentNumber === $loan->term_months) {
            $principalAmount = $remainingBalance;
            $interestAmount = 0;
        }
        
        $statuses = [
            LoanSchedule::STATUS_PENDING,
            LoanSchedule::STATUS_PAID,
            LoanSchedule::STATUS_OVERDUE,
        ];
        
        $status = $this->faker->randomElement($statuses);
        
        // Use a safe date range: start_date + months (don't modify original date)
        $dueDate = \Carbon\Carbon::parse($loan->start_date)->addMonths($installmentNumber);
        
        return [
            'loan_id' => $loan->id,
            'installment_number' => $installmentNumber,
            'amount_due' => $monthlyPayment,
            'principal_amount' => $principalAmount,
            'interest_amount' => $interestAmount,
            'due_date' => $dueDate,
            'status' => $status,
            'paid_at' => $status === LoanSchedule::STATUS_PAID ? 
                $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'penalty_amount' => $status === LoanSchedule::STATUS_OVERDUE ? 
                $this->faker->numberBetween(10000, 100000) : 0,
        ];
    }

    /**
     * Indicate that the installment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanSchedule::STATUS_PENDING,
            'paid_at' => null,
            'penalty_amount' => 0,
        ]);
    }

    /**
     * Indicate that the installment is paid.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $dueDate = \Carbon\Carbon::parse($attributes['due_date'] ?? now());
            return [
                'status' => LoanSchedule::STATUS_PAID,
                'paid_at' => $this->faker->dateTimeBetween(
                    max($dueDate->subMonths(1), now()->subMonths(1)),
                    'now'
                ),
                'penalty_amount' => 0,
            ];
        });
    }

    /**
     * Indicate that the installment is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanSchedule::STATUS_OVERDUE,
            'paid_at' => null,
            'penalty_amount' => $this->faker->numberBetween(10000, 100000),
        ]);
    }
}
