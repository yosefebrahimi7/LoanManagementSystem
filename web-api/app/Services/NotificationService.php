<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Services\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Centralized notification service
 * Provides easy methods to dispatch notifications throughout the application
 */
class NotificationService implements NotificationServiceInterface
{
    /**
     * Send loan approval notification
     */
    public function notifyLoanApproval(User $user, $loan): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_LOAN_APPROVAL,
            $user,
            ['loan' => $loan]
        );
    }

    /**
     * Send loan rejection notification
     */
    public function notifyLoanRejection(User $user, $loan, string $reason): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_LOAN_REJECTION,
            $user,
            ['loan' => $loan, 'reason' => $reason]
        );
    }

    /**
     * Send payment success notification
     */
    public function notifyPaymentSuccess(User $user, array $data): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_PAYMENT_SUCCESS,
            $user,
            $data
        );
    }

    /**
     * Send payment failed notification
     */
    public function notifyPaymentFailed(User $user, array $data): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_PAYMENT_FAILED,
            $user,
            $data
        );
    }

    /**
     * Send wallet insufficient balance notification
     */
    public function notifyWalletInsufficient(User $user, int $currentBalance, int $requiredAmount): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_WALLET_INSUFFICIENT,
            $user,
            [
                'message' => 'موجودی کیف پول شما کافی نیست.',
                'current_balance' => $currentBalance,
                'required_amount' => $requiredAmount,
            ]
        );
    }

    /**
     * Send loan fully paid notification
     */
    public function notifyLoanFullyPaid(User $user, $loan): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_LOAN_FULLY_PAID,
            $user,
            ['loan' => $loan]
        );
    }

    /**
     * Send admin wallet low balance notification to all admins
     */
    public function notifyAdminWalletLow(int $currentBalance, int $requiredAmount): void
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            SendNotificationJob::dispatch(
                SendNotificationJob::TYPE_ADMIN_WALLET_LOW,
                $admin,
                [
                    'message' => 'موجودی کیف پول مشترک ادمین ها کافی نیست.',
                    'current_balance' => $currentBalance,
                    'required_amount' => $requiredAmount,
                ]
            );
        }
    }

    /**
     * Send wallet recharge success notification
     */
    public function notifyWalletRechargeSuccess(User $user, array $data): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_WALLET_RECHARGE_SUCCESS,
            $user,
            $data
        );
    }

    /**
     * Send wallet recharge failed notification
     */
    public function notifyWalletRechargeFailed(User $user, array $data): void
    {
        SendNotificationJob::dispatch(
            SendNotificationJob::TYPE_WALLET_RECHARGE_FAILED,
            $user,
            $data
        );
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, int $limit): array
    {
        $notifications = $user->notifications()->latest()->limit($limit)->get();
        
        return [
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * Delete notification
     */
    public function deleteNotification(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->delete();
    }
}
