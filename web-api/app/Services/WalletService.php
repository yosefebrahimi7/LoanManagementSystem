<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Services\Interfaces\WalletServiceInterface;
use App\Services\ZarinpalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WalletService implements WalletServiceInterface
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private ZarinpalService $zarinpalService
    ) {}

    /**
     * Translate error messages to Persian
     */
    private function translateErrorMessage(string $message): string
    {
        $translations = [
            'Payment request failed: The amount must not be greater than 20000000000.' => 'مبلغ بیشتر از حد مجاز است. حداکثر مبلغ ۲,۰۰۰,۰۰۰,۰۰۰ تومان است.',
            'amount must not be greater than 20000000000' => 'مبلغ بیشتر از حد مجاز است. حداکثر مبلغ ۲,۰۰۰,۰۰۰,۰۰۰ تومان است.',
            'amount must not be greater' => 'مبلغ بیشتر از حد مجاز است',
            'Unknown error' => 'خطای ناشناخته',
        ];

        // Try to find exact match
        if (isset($translations[$message])) {
            return $translations[$message];
        }

        // Try to match partial messages
        foreach ($translations as $english => $persian) {
            if (stripos($message, $english) !== false || stripos($message, str_replace('Payment request failed: ', '', $english)) !== false) {
                return $persian;
            }
        }

        // If no match, return a generic Persian error message
        return 'خطا در اتصال به درگاه پرداخت. لطفاً دوباره تلاش کنید.';
    }

    /**
     * Get user's wallet (user or admin based on role)
     */
    public function getWallet(\App\Models\User $user): Wallet
    {
        // If user is admin, return shared admin wallet
        if ($user->isAdmin()) {
            return $this->walletRepository->getOrCreateSharedAdminWallet();
        }

        // Regular users get their own wallet or create one
        $wallet = $this->walletRepository->findByUserId($user->id);
        
        if (!$wallet) {
            $wallet = $this->walletRepository->create([
                'user_id' => $user->id,
                'balance' => 0,
                'currency' => 'IRR',
                'is_shared' => false,
            ]);
        }
        
        return $wallet;
    }

    /**
     * Get wallet transactions
     */
    public function getTransactions(Wallet $wallet, int $page = 1, int $perPage = 10): array
    {
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ];
    }

    /**
     * Initiate wallet recharge
     */
    public function initiateRecharge(Wallet $wallet, int $amount): array
    {
        DB::beginTransaction();
        try {
            // Store transaction amount in Rials (1 Toman = 10 Rials)
            $amountInRials = $amount * 10;
            
            // Create transaction record for pending recharge
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $amountInRials, // Store in Rials to match wallet balance unit
                'balance_after' => $wallet->balance,
                'description' => 'شارژ کیف پول',
                'meta' => ['status' => 'pending', 'amount_in_tomans' => $amount],
            ]);

            // Request payment from Zarinpal
            $description = $wallet->is_shared 
                ? "شارژ کیف پول مشترک ادمین" 
                : "شارژ کیف پول کاربر";

            // Use base URL from config and build wallet callback URL
            $baseUrl = config('app.url', 'http://localhost:8000');
            $callbackUrl = rtrim($baseUrl, '/') . '/api/wallet/callback';
            
            // Zarinpal expects amount in Rials, so convert from Tomans
            $result = $this->zarinpalService->requestPayment(
                amount: $amountInRials, // Send in Rials for Zarinpal
                description: $description,
                callbackUrl: $callbackUrl,
                metadata: [
                    'wallet_id' => $wallet->id,
                    'transaction_id' => $transaction->id,
                    'type' => 'wallet_recharge',
                ]
            );

            if (!$result['success']) {
                $transaction->update([
                    'meta' => ['status' => 'failed', 'error' => 'Failed to connect to payment gateway'],
                ]);
                DB::rollBack();
                throw new \Exception('امکان اتصال به درگاه پرداخت وجود ندارد');
            }

            // Update transaction with gateway reference
            $transaction->update([
                'meta' => array_merge($transaction->meta ?? [], [
                    'status' => 'pending',
                    'authority' => $result['authority'],
                ]),
            ]);

            DB::commit();

            return [
                'success' => true,
                'payment_url' => $result['payment_url'],
                'authority' => $result['authority'],
                'transaction_id' => $transaction->id,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet recharge initiation failed', [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            // Translate error message to Persian
            $persianMessage = $this->translateErrorMessage($e->getMessage());
            throw new \Exception($persianMessage);
        }
    }

    /**
     * Process recharge callback from gateway
     */
    public function processRechargeCallback(array $gatewayData): mixed
    {
        try {
            $authority = $gatewayData['Authority'] ?? null;
            $status = $gatewayData['Status'] ?? null;

            Log::info('Wallet recharge callback received', [
                'authority' => $authority,
                'status' => $status,
            ]);

            if (!$authority) {
                return redirect(config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/failed?error=' . urlencode('Authority not provided'));
            }

            // Find transaction by authority
            $transaction = WalletTransaction::where('meta->authority', $authority)->first();

            if (!$transaction) {
                Log::error('Wallet transaction not found', ['authority' => $authority]);
                return redirect(config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/failed?error=' . urlencode('تراکنش یافت نشد'));
            }

            $wallet = $transaction->wallet;

            // If transaction is already completed
            if (isset($transaction->meta['status']) && $transaction->meta['status'] === 'completed') {
                return redirect(config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/success?transaction_id=' . $transaction->id);
            }

            if ($status === 'OK') {
                // Verify payment (amount is already in Rials)
                $verifyResult = $this->zarinpalService->verifyPayment($authority, $transaction->amount);

                if ($verifyResult['success']) {
                    $this->completeRecharge($transaction, $wallet, $verifyResult);
                    
                    $successUrl = config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/success?transaction_id=' . $transaction->id;
                    return redirect($successUrl);
                }
                
                // Code 101 means payment already verified
                if (isset($verifyResult['code']) && $verifyResult['code'] == 101) {
                    Log::info('Wallet payment already verified (code 101)', ['transaction_id' => $transaction->id]);
                    $this->completeRecharge($transaction, $wallet, $verifyResult);
                    
                    $successUrl = config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/success?transaction_id=' . $transaction->id;
                    return redirect($successUrl);
                }

                // Payment verification failed
                $this->failRecharge($transaction, 'Verification failed: ' . ($verifyResult['message'] ?? 'Unknown error'));

                Log::error('Wallet payment verification failed', [
                    'transaction_id' => $transaction->id,
                    'error' => $verifyResult['message'] ?? 'Unknown error',
                ]);

                $failedUrl = config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/failed?transaction_id=' . $transaction->id;
                return redirect($failedUrl);
            }

            // Status is not OK - user cancelled
            $this->failRecharge($transaction, 'User cancelled or payment failed');

            Log::warning('Wallet payment not OK', [
                'transaction_id' => $transaction->id,
                'status' => $status,
            ]);

            $failedUrl = config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/failed?transaction_id=' . $transaction->id;
            return redirect($failedUrl);

        } catch (\Exception $e) {
            Log::error('Wallet recharge callback failed', [
                'error' => $e->getMessage(),
                'authority' => $gatewayData['Authority'] ?? null,
            ]);

            return redirect(config('app.frontend_url', 'http://localhost:5174') . '/wallet/recharge/failed?error=' . urlencode('خطا در پردازش بازگشت از درگاه'));
        }
    }

    /**
     * Complete recharge and update wallet balance
     */
    private function completeRecharge(WalletTransaction $transaction, Wallet $wallet, array $verifyResult): void
    {
        DB::beginTransaction();
        try {
            // Update transaction
            $transaction->update([
                'balance_after' => $wallet->balance + $transaction->amount,
                'meta' => array_merge($transaction->meta ?? [], [
                    'status' => 'completed',
                    'gateway_response' => $verifyResult,
                    'completed_at' => now()->toDateTimeString(),
                ]),
            ]);

            // Update wallet balance
            $wallet->increment('balance', $transaction->amount);
            
            // Clear cache
            Cache::forget("wallet.user.{$wallet->user_id}");
            Cache::forget("wallet.balance.{$wallet->id}");
            Cache::forget("wallet.shared.admin");

            DB::commit();

            Log::info('Wallet recharge completed', [
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'amount' => $transaction->amount,
                'balance_after' => $wallet->fresh()->balance,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet recharge completion failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Fail recharge
     */
    private function failRecharge(WalletTransaction $transaction, string $reason): void
    {
        $transaction->update([
            'meta' => array_merge($transaction->meta ?? [], [
                'status' => 'failed',
                'error' => $reason,
                'failed_at' => now()->toDateTimeString(),
            ]),
        ]);
    }
}

