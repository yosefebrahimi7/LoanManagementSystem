<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing admin user
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        
        // Get existing regular users (limit to 2)
        $users = User::where('role', User::ROLE_USER)
            ->limit(2)
            ->get()
            ->toArray();
        
        // Create wallets for users if they don't exist
        if ($admin) {
            $this->createWalletForUser($admin);
        }
        foreach ($users as $user) {
            $userModel = User::find($user['id']);
            if ($userModel) {
                $this->createWalletForUser($userModel);
            }
        }
        
        // Only create loans if we have enough users
        if (count($users) >= 2 && $admin) {
            // Convert array users to models
            $userModels = array_map(function($user) {
                return User::find($user['id']);
            }, $users);
            
            // Create loans at various stages (simplified for 2 users)
            $this->createLoansForLimitedUsers($userModels, $admin);
        }
    }

    private function createSettings(): void
    {
        $settings = [
            [
                'key' => 'default_interest_rate',
                'value' => '14.5',
                'description' => 'Default interest rate for loans (%)'
            ],
            [
                'key' => 'penalty_rate',
                'value' => '0.5',
                'description' => 'Penalty rate per day for overdue payments (%)'
            ],
            [
                'key' => 'max_loan_amount',
                'value' => '100000000',
                'description' => 'Maximum loan amount (IRR)'
            ],
            [
                'key' => 'min_loan_amount',
                'value' => '1000000',
                'description' => 'Minimum loan amount (IRR)'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    private function createWalletForUser(User $user): void
    {
        Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'balance' => rand(0, 5000000), // 0 to 5M Rials (0 to 500K Tomans)
                'currency' => 'IRR',
                'is_shared' => false,
            ]
        );
    }

    private function createLoansForLimitedUsers(array $users, User $admin): void
    {
        // Check if loans already exist
        $existingLoans = Loan::count();
        
        if ($existingLoans > 0) {
            // Loans already exist, skip creation
            return;
        }

        // Create 1 pending loan for first user
        Loan::factory()->pending()->create([
            'user_id' => $users[0]->id,
        ]);

        // Create 1 approved loan for second user
        $loan = Loan::factory()->approved()->create([
            'user_id' => $users[1]->id,
            'approved_by' => $admin->id,
        ]);
        $this->generatePaymentSchedule($loan);

        // Create 1 active loan for first user
        $loan = Loan::factory()->active()->create([
            'user_id' => $users[0]->id,
            'approved_by' => $admin->id,
        ]);
        $this->generatePaymentSchedule($loan);
    }

    private function generatePaymentSchedule(Loan $loan): void
    {
        // Check if payment schedule already exists for this loan
        $existingSchedules = LoanSchedule::where('loan_id', $loan->id)->count();
        
        if ($existingSchedules > 0) {
            // Payment schedule already exists, skip creation
            return;
        }

        $monthlyPayment = $loan->monthly_payment;
        $remainingBalance = $loan->amount;
        $startDate = $loan->start_date;
        $interestRate = $loan->interest_rate / 100 / 12;

        for ($i = 1; $i <= $loan->term_months; $i++) {
            $dueDate = $startDate->copy()->addMonths($i);
            
            // Calculate principal and interest portions
            $interestAmount = intval(round($remainingBalance * $interestRate));
            $principalAmount = $monthlyPayment - $interestAmount;
            
            // For the last payment, adjust to remaining balance
            if ($i === $loan->term_months) {
                $principalAmount = $remainingBalance;
                $interestAmount = 0;
            }

            // Determine status based on loan status and due date
            $status = LoanSchedule::STATUS_PENDING;
            $paidAt = null;
            $penaltyAmount = 0;

            if ($loan->status === Loan::STATUS_PAID) {
                $status = LoanSchedule::STATUS_PAID;
                $paidAt = $dueDate->copy()->addDays(rand(1, 5));
            } elseif ($loan->status === Loan::STATUS_DELINQUENT && $dueDate < now()) {
                $status = LoanSchedule::STATUS_OVERDUE;
                $penaltyAmount = rand(10000, 100000);
            }

            LoanSchedule::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'amount_due' => $monthlyPayment,
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'due_date' => $dueDate,
                'status' => $status,
                'paid_at' => $paidAt,
                'penalty_amount' => $penaltyAmount,
            ]);

            $remainingBalance -= $principalAmount;
        }
    }
}
