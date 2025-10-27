<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface NotificationServiceInterface
{
    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, int $limit = 10): array;

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
}

