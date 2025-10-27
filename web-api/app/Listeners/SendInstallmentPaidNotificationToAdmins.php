<?php

namespace App\Listeners;

use App\Events\InstallmentPaid;
use App\Models\User;
use App\Notifications\InstallmentPaidAdminNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInstallmentPaidNotificationToAdmins implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InstallmentPaid $event): void
    {
        // Get all admin users
        $admins = User::where('role', User::ROLE_ADMIN)->get();

        // Send notification to all admins
        foreach ($admins as $admin) {
            $admin->notify(new InstallmentPaidAdminNotification($event->installment));
        }
    }
}

