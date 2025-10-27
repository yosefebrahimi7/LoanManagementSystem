<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanPaymentFactory extends Factory
{
    protected $model = LoanPayment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'loan_id' => Loan::factory(),
            'loan_schedule_id' => LoanSchedule::factory(),
            'amount' => $this->faker->numberBetween(100000, 5000000),
            'payment_method' => LoanPayment::METHOD_ZARINPAL,
            'status' => LoanPayment::STATUS_COMPLETED,
            'gateway_reference' => $this->faker->uuid(),
            'gateway_response' => [
                'code' => 100,
                'message' => 'Transaction successful',
            ],
            'notes' => null,
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanPayment::STATUS_FAILED,
            'notes' => 'Payment failed',
        ]);
    }

    /**
     * Create a payment with a specific schedule.
     */
    public function forSchedule(LoanSchedule $schedule): static
    {
        return $this->state(fn (array $attributes) => [
            'loan_schedule_id' => $schedule->id,
            'amount' => $schedule->amount_due,
        ]);
    }
}

