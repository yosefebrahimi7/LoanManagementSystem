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
        // Create settings
        $this->createSettings();
        
        // Create admin user
        $admin = $this->createAdminUser();
        
        // Create regular users
        $users = $this->createRegularUsers();
        
        // Create wallets for users
        $this->createWallets($users);
        
        // Create loans at various stages
        $this->createLoansAtVariousStages($users, $admin);
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

    private function createAdminUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => User::ROLE_ADMIN,
            ]
        );
    }

    private function createRegularUsers(): array
    {
        $users = [];
        
        // Always create demo users to ensure we have enough users for loans
        for ($i = 1; $i <= 10; $i++) {
            $email = "user{$i}@example.com";
            $users[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => "User{$i}",
                    'last_name' => "LastName{$i}",
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'role' => User::ROLE_USER,
                ]
            );
        }
        
        return $users;
    }

    private function createWallets(array $users): void
    {
        foreach ($users as $user) {
            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'balance' => rand(0, 5000000), // 0 to 50M IRR
                    'currency' => 'IRR',
                ]
            );
        }
    }

    private function createLoansAtVariousStages(array $users, User $admin): void
    {
        // Check if loans already exist for these users
        $existingLoans = Loan::whereIn('user_id', collect($users)->pluck('id'))->count();
        
        if ($existingLoans > 0) {
            // Loans already exist, skip creation
            return;
        }

        // Create pending loans (5)
        for ($i = 0; $i < 5; $i++) {
            $loan = Loan::factory()->pending()->create([
                'user_id' => $users[$i]->id,
            ]);
        }

        // Create approved loans (3)
        for ($i = 0; $i < 3; $i++) {
            $loan = Loan::factory()->approved()->create([
                'user_id' => $users[$i + 5]->id,
                'approved_by' => $admin->id,
            ]);
            
            // Generate payment schedules for approved loans
            $this->generatePaymentSchedule($loan);
        }

        // Create active loans (2)
        for ($i = 0; $i < 2; $i++) {
            $loan = Loan::factory()->active()->create([
                'user_id' => $users[$i + 8]->id,
                'approved_by' => $admin->id,
            ]);
            
            // Generate payment schedules for active loans
            $this->generatePaymentSchedule($loan);
        }

        // Create rejected loans (2)
        for ($i = 0; $i < 2; $i++) {
            Loan::factory()->rejected()->create([
                'user_id' => $users[$i]->id,
                'approved_by' => $admin->id,
                'rejection_reason' => 'Insufficient credit history',
            ]);
        }

        // Create delinquent loans (1)
        $loan = Loan::factory()->delinquent()->create([
            'user_id' => $users[9]->id,
            'approved_by' => $admin->id,
        ]);
        
        // Generate payment schedules for delinquent loan
        $this->generatePaymentSchedule($loan);
        
        // Create paid loans (1)
        $loan = Loan::factory()->paid()->create([
            'user_id' => $users[0]->id,
            'approved_by' => $admin->id,
        ]);
        
        // Generate payment schedules for paid loan
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
