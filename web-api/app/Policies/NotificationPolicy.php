<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * Determine whether the user can view any notifications.
     */
    public function viewAny(): bool
    {
        return true; // All authenticated users can view their notifications
    }

    /**
     * Determine whether the user can view the notification.
     */
    public function view($user, DatabaseNotification $notification): bool
    {
        // User can only view their own notifications
        return $notification->notifiable_id === $user->id;
    }

    /**
     * Determine whether the user can mark notification as read.
     */
    public function markAsRead($user, DatabaseNotification $notification): bool
    {
        // User can only mark their own notifications as read
        return $notification->notifiable_id === $user->id;
    }

    /**
     * Determine whether the user can delete the notification.
     */
    public function delete($user, DatabaseNotification $notification): bool
    {
        // User can only delete their own notifications
        return $notification->notifiable_id === $user->id;
    }
}
