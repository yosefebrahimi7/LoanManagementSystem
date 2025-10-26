<?php

namespace App\Services;

use App\Events\LoanApproved;
use App\Events\LoanRejected;
use App\Exceptions\LoanException;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private LoanRepositoryInterface $loanRepository
    ) {}
    /**
     * Create a new loan request
     */
    public function createLoanRequest(User $user, array $data): Loan
    {
        // Get default interest rate from settings if not provided
        $interestRate = $data['interest_rate'] ?? $this->getDefaultInterestRate();
        
        // Calculate monthly payment
        $monthlyPayment = $this->calculateMonthlyPayment(
            $data['amount'],
            $interestRate,
            $data['term_months']
        );

        return DB::transaction(function () use ($user, $data, $interestRate, $monthlyPayment) {
            $loan = $this->loanRepository->create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'term_months' => $data['term_months'],
                'interest_rate' => $interestRate,
                'monthly_payment' => $monthlyPayment,
                'remaining_balance' => $data['amount'],
                'status' => Loan::STATUS_PENDING,
                'start_date' => $data['start_date'],
            ]);

            return $loan;
        });
    }

    /**
     * Process loan approval or rejection
     */
    public function processLoanApproval(Loan $loan, User $admin, array $data): array
    {
        return DB::transaction(function () use ($loan, $admin, $data) {
            if ($data['action'] === 'approve') {
                $this->loanRepository->update($loan->id, [
                    'status' => Loan::STATUS_APPROVED,
                    'approved_at' => now(),
                    'approved_by' => $admin->id,
                ]);

                // Generate payment schedule
                $this->generatePaymentSchedule($loan);

                // Fire loan approved event
                event(new LoanApproved($loan->fresh()));

                return [
                    'message' => 'Loan approved successfully',
                    'loan' => $loan->fresh(['schedules'])
                ];
            } else {
                $this->loanRepository->update($loan->id, [
                    'status' => Loan::STATUS_REJECTED,
                    'rejection_reason' => $data['rejection_reason'],
                    'approved_by' => $admin->id,
                ]);

                // Fire loan rejected event
                event(new LoanRejected($loan->fresh()));

                return [
                    'message' => 'Loan rejected successfully',
                    'loan' => $loan->fresh()
                ];
            }
        });
    }

    /**
     * Generate payment schedule for approved loan
     */
    private function generatePaymentSchedule(Loan $loan): void
    {
        $monthlyPayment = $loan->monthly_payment;
        $remainingBalance = $loan->amount;
        $startDate = Carbon::parse($loan->start_date);
        $interestRate = $loan->interest_rate / 100 / 12; // Monthly interest rate

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

            LoanSchedule::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'amount_due' => $monthlyPayment,
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'due_date' => $dueDate,
                'status' => LoanSchedule::STATUS_PENDING,
            ]);

            $remainingBalance -= $principalAmount;
        }
    }

    /**
     * Calculate monthly payment using PMT formula
     */
    private function calculateMonthlyPayment(int $amount, float $interestRate, int $termMonths): int
    {
        if ($interestRate == 0) {
            return intval($amount / $termMonths);
        }

        $monthlyRate = $interestRate / 100 / 12;
        $monthlyPayment = $amount * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / 
                         (pow(1 + $monthlyRate, $termMonths) - 1);

        return intval(round($monthlyPayment));
    }

    /**
     * Get user's loans
     */
    public function getUserLoans(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Loan::where('user_id', $user->id)
            ->with(['schedules', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get loan by ID with authorization check
     */
    public function getLoanById(int $loanId, User $user): ?Loan
    {
        $loan = Loan::with(['schedules', 'payments', 'approvedBy', 'user'])
            ->find($loanId);

        if (!$loan) {
            return null;
        }

        // Admin can see all loans, users can only see their own
        if (!$user->isAdmin() && $loan->user_id !== $user->id) {
            return null;
        }

        return $loan;
    }

    /**
     * Get all loans for admin
     */
    public function getAllLoans(): \Illuminate\Database\Eloquent\Collection
    {
        return Loan::with(['user', 'schedules', 'payments', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Validate loan approval eligibility
     */
    public function canApproveLoan(Loan $loan): bool
    {
        return $loan->status === Loan::STATUS_PENDING;
    }

    /**
     * Get default interest rate from settings
     */
    private function getDefaultInterestRate(): float
    {
        $setting = Setting::where('key', 'default_interest_rate')->first();
        return $setting ? floatval($setting->value) : 14.5;
    }
}
