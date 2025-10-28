<?php

namespace App\Jobs;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job to add approved loan amount to user's wallet
 */
class DeductFromUserWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public int $amount
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WalletRepositoryInterface $walletRepository): void
    {
        try {
            DB::transaction(function () use ($walletRepository) {
                // Lock and get wallet
                $wallet = Wallet::lockForUpdate()
                    ->where('user_id', $this->userId)
                    ->where('is_shared', false)
                    ->first();
                
                if (!$wallet) {
                    // Create wallet if it doesn't exist
                    $wallet = $walletRepository->create([
                        'user_id' => $this->userId,
                        'balance' => 0,
                        'currency' => 'IRR',
                        'is_shared' => false,
                    ]);
                }
                
                // Add approved loan amount to user's wallet
                $walletRepository->updateBalance($wallet->id, $this->amount);
                
                // Create transaction record
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransaction::TYPE_CREDIT,
                    'amount' => $this->amount,
                    'balance_after' => $wallet->fresh()->balance,
                    'description' => "تایید وام و واریز مبلغ",
                    'meta' => [
                        'type' => 'loan_approved',
                    ],
                ]);
                
                Log::info('Loan amount added to user wallet', [
                    'user_id' => $this->userId,
                    'wallet_id' => $wallet->id,
                    'amount' => $this->amount,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to add loan amount to user wallet', [
                'user_id' => $this->userId,
                'amount' => $this->amount,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw the exception so the job can be retried
            throw $e;
        }
    }
}

