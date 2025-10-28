<?php

namespace App\Services;

use App\Events\LoanApproved;
use App\Events\LoanRejected;
use App\Events\LoanRequested;
use App\Exceptions\LoanException;
use App\Jobs\DeductFromUserWalletJob;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private LoanRepositoryInterface $loanRepository,
        private WalletRepositoryInterface $walletRepository,
        private NotificationService $notificationService
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

            // Fire loan requested event to notify admins
            event(new LoanRequested($loan));

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
                // Check if admin wallet has sufficient balance
                $adminWallet = $this->walletRepository->getOrCreateSharedAdminWallet();
                $loanAmount = $loan->amount;
                
                // Convert loan amount to the wallet unit (Rials if needed)
                // Loan amounts are stored in Tomans, wallet is in Rials (1 Toman = 10 Rials)
                $loanAmountInRials = $loanAmount * 10;
                
                if (!$this->walletRepository->hasSufficientBalance($adminWallet->id, $loanAmountInRials)) {
                    // Notify all admins about insufficient wallet balance
                    $this->notificationService->notifyAdminWalletLow(
                        $adminWallet->balance,
                        $loanAmountInRials
                    );
                    
                    throw LoanException::badRequest(
                        'موجودی کیف پول مشترک ادمین ها کافی نیست. موجودی: ' . 
                        number_format($adminWallet->balance / 100, 0) . 
                        ' تومان. مبلغ مورد نیاز: ' . 
                        number_format($loanAmountInRials / 100, 0) . 
                        ' تومان. لطفاً کیف پول را شارژ کنید.'
                    );
                }

                // Deduct from admin wallet within the transaction
                DB::transaction(function () use ($adminWallet, $loanAmountInRials, $loan) {
                    // Lock admin wallet for update
                    $lockedWallet = \App\Models\Wallet::lockForUpdate()->find($adminWallet->id);
                    
                    if (!$lockedWallet || $lockedWallet->balance < $loanAmountInRials) {
                        throw LoanException::badRequest('خطا در کسر از کیف پول ادمین');
                    }
                    
                    // Deduct from admin wallet
                    $lockedWallet->decrement('balance', $loanAmountInRials);
                });

                // Add to user's wallet via background job (outside transaction)
                DeductFromUserWalletJob::dispatch($loan->user_id, $loanAmountInRials);

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
