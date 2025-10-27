<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\NotificationServiceInterface;

class NotificationService implements NotificationServiceInterface
{
    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, int $limit = 10): array
    {
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        return [
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            }),
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
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->delete();
        }
    }
}
