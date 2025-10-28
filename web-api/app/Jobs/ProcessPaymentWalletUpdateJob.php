<?php

namespace App\Jobs;

use App\Models\LoanPayment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Job to process wallet updates after installment payment
 * - Deduct from user's wallet
 * - Add to admin's shared wallet
 */
class ProcessPaymentWalletUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public int $adminWalletId,
        public int $amountInRials,
        public int $paymentId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WalletRepositoryInterface $walletRepository): void
    {
        try {
            DB::transaction(function () use ($walletRepository) {
                // Lock user's wallet for update
                $userWallet = Wallet::lockForUpdate()
                    ->where('user_id', $this->userId)
                    ->where('is_shared', false)
                    ->first();
                
                if (!$userWallet) {
                    throw new \Exception("User wallet not found for user ID: {$this->userId}");
                }

                // Lock admin wallet for update
                $adminWallet = Wallet::lockForUpdate()->find($this->adminWalletId);
                
                if (!$adminWallet) {
                    throw new \Exception("Admin wallet not found. Wallet ID: {$this->adminWalletId}");
                }

                // Check if user has sufficient balance (double-check with lock)
                if ($userWallet->balance < $this->amountInRials) {
                    throw new \Exception("Insufficient balance. Available: {$userWallet->balance}, Required: {$this->amountInRials}");
                }

                // Deduct from user's wallet (atomic operation)
                $userWallet->decrement('balance', $this->amountInRials);
                
                // Get loan_id from payment record
                $payment = \App\Models\LoanPayment::find($this->paymentId);
                
                // Create transaction record for user wallet
                WalletTransaction::create([
                    'wallet_id' => $userWallet->id,
                    'type' => WalletTransaction::TYPE_DEBIT,
                    'amount' => $this->amountInRials,
                    'balance_after' => $userWallet->fresh()->balance,
                    'description' => "پرداخت اقساط وام",
                    'meta' => [
                        'payment_id' => $this->paymentId,
                        'loan_id' => $payment?->loan_id,
                    ],
                ]);

                // Add to admin's shared wallet (atomic operation)
                $adminWallet->increment('balance', $this->amountInRials);
                
                // Create transaction record for admin wallet
                WalletTransaction::create([
                    'wallet_id' => $this->adminWalletId,
                    'type' => WalletTransaction::TYPE_CREDIT,
                    'amount' => $this->amountInRials,
                    'balance_after' => $adminWallet->fresh()->balance,
                    'description' => "دریافت اقساط وام",
                    'meta' => [
                        'payment_id' => $this->paymentId,
                        'user_id' => $this->userId,
                    ],
                ]);

                // Clear cache
                \Illuminate\Support\Facades\Cache::forget("wallet.user.{$this->userId}");
                \Illuminate\Support\Facades\Cache::forget("wallet.balance.{$userWallet->id}");
                \Illuminate\Support\Facades\Cache::forget("wallet.balance.{$this->adminWalletId}");
                \Illuminate\Support\Facades\Cache::forget("wallet.shared.admin");

                Log::info('Wallet updates completed for payment', [
                    'payment_id' => $this->paymentId,
                    'user_id' => $this->userId,
                    'amount' => $this->amountInRials,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to process payment wallet updates', [
                'payment_id' => $this->paymentId,
                'user_id' => $this->userId,
                'amount' => $this->amountInRials,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw the exception so the job can be retried
            throw $e;
        }
    }
}

