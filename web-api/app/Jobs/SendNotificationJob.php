<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\LoanApprovalNotification;
use App\Notifications\LoanRejectionNotification;
use App\Notifications\PaymentConfirmationNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\WalletInsufficientBalanceNotification;
use App\Notifications\LoanFullyPaidNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Centralized notification job dispatcher
 * Handles all notification types in the system
 */
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TYPE_LOAN_APPROVAL = 'loan_approval';
    public const TYPE_LOAN_REJECTION = 'loan_rejection';
    public const TYPE_PAYMENT_SUCCESS = 'payment_success';
    public const TYPE_PAYMENT_FAILED = 'payment_failed';
    public const TYPE_WALLET_INSUFFICIENT = 'wallet_insufficient';
    public const TYPE_LOAN_FULLY_PAID = 'loan_fully_paid';
    public const TYPE_WALLET_RECHARGE_SUCCESS = 'wallet_recharge_success';
    public const TYPE_WALLET_RECHARGE_FAILED = 'wallet_recharge_failed';
    public const TYPE_ADMIN_WALLET_LOW = 'admin_wallet_low';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $type,
        public User $user,
        public array $data = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            switch ($this->type) {
                case self::TYPE_LOAN_APPROVAL:
                    $this->sendLoanApproval($this->user, $this->data);
                    break;
                
                case self::TYPE_LOAN_REJECTION:
                    $this->sendLoanRejection($this->user, $this->data);
                    break;
                
                case self::TYPE_PAYMENT_SUCCESS:
                    $this->sendPaymentSuccess($this->user, $this->data);
                    break;
                
                case self::TYPE_PAYMENT_FAILED:
                    $this->sendPaymentFailed($this->user, $this->data);
                    break;
                
                case self::TYPE_WALLET_INSUFFICIENT:
                    $this->sendWalletInsufficient($this->user, $this->data);
                    break;
                
                case self::TYPE_LOAN_FULLY_PAID:
                    $this->sendLoanFullyPaid($this->user, $this->data);
                    break;
                
                case self::TYPE_WALLET_RECHARGE_SUCCESS:
                    $this->sendWalletRechargeSuccess($this->user, $this->data);
                    break;
                
                case self::TYPE_WALLET_RECHARGE_FAILED:
                    $this->sendWalletRechargeFailed($this->user, $this->data);
                    break;
                
                case self::TYPE_ADMIN_WALLET_LOW:
                    $this->sendAdminWalletLow($this->user, $this->data);
                    break;
                
                default:
                    Log::warning("Unknown notification type: {$this->type}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'type' => $this->type,
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendLoanApproval(User $user, array $data): void
    {
        $user->notify(new LoanApprovalNotification($data['loan']));
    }

    private function sendLoanRejection(User $user, array $data): void
    {
        $user->notify(new LoanRejectionNotification($data['loan'], $data['reason']));
    }

    private function sendPaymentSuccess(User $user, array $data): void
    {
        $user->notify(new PaymentConfirmationNotification($data));
    }

    private function sendPaymentFailed(User $user, array $data): void
    {
        $user->notify(new PaymentFailedNotification($data));
    }

    private function sendWalletInsufficient(User $user, array $data): void
    {
        Notification::send($user, new WalletInsufficientBalanceNotification($data));
    }

    private function sendLoanFullyPaid(User $user, array $data): void
    {
        $user->notify(new LoanFullyPaidNotification($data['loan']));
    }

    private function sendWalletRechargeSuccess(User $user, array $data): void
    {
        // You can create specific notification classes later
        Log::info('Wallet recharge successful', ['user_id' => $user->id, 'data' => $data]);
    }

    private function sendWalletRechargeFailed(User $user, array $data): void
    {
        Log::info('Wallet recharge failed', ['user_id' => $user->id, 'data' => $data]);
    }

    private function sendAdminWalletLow(User $user, array $data): void
    {
        // Notify all admins about low wallet balance
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new WalletInsufficientBalanceNotification($data));
    }
}

