<?php

namespace App\Listeners;

use App\Events\LoanRequested;
use App\Models\User;
use App\Notifications\NewLoanRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLoanRequestNotificationToAdmins implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LoanRequested $event): void
    {
        // Get all admin users
        $admins = User::where('role', User::ROLE_ADMIN)->get();

        // Send notification to all admins
        foreach ($admins as $admin) {
            $admin->notify(new NewLoanRequestNotification($event->loan));
        }
    }
}

