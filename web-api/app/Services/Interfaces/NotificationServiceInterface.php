<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface NotificationServiceInterface
{
    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, int $limit): array;

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(User $user): int;

    /**
     * Mark notification as read
     */
    public function markAsRead(User $user, string $notificationId): void;

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): void;

    /**
     * Delete notification
     */
    public function deleteNotification(User $user, string $notificationId): void;

    /**
     * Send loan approval notification
     */
    public function notifyLoanApproval(User $user, $loan): void;

    /**
     * Send loan rejection notification
     */
    public function notifyLoanRejection(User $user, $loan, string $reason): void;

    /**
     * Send payment success notification
     */
    public function notifyPaymentSuccess(User $user, array $data): void;

    /**
     * Send payment failed notification
     */
    public function notifyPaymentFailed(User $user, array $data): void;

    /**
     * Send wallet insufficient balance notification
     */
    public function notifyWalletInsufficient(User $user, int $currentBalance, int $requiredAmount): void;

    /**
     * Send loan fully paid notification
     */
    public function notifyLoanFullyPaid(User $user, $loan): void;

    /**
     * Send admin wallet low balance notification to all admins
     */
    public function notifyAdminWalletLow(int $currentBalance, int $requiredAmount): void;

    /**
     * Send wallet recharge success notification
     */
    public function notifyWalletRechargeSuccess(User $user, array $data): void;

    /**
     * Send wallet recharge failed notification
     */
    public function notifyWalletRechargeFailed(User $user, array $data): void;
}
