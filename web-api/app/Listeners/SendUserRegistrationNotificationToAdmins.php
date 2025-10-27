<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\User;
use App\Notifications\UserRegistrationAdminNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendUserRegistrationNotificationToAdmins implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Get all admin users
        $admins = User::where('role', User::ROLE_ADMIN)->get();

        // Send notification to all admins
        foreach ($admins as $admin) {
            $admin->notify(new UserRegistrationAdminNotification($event->user));
        }
    }
}

