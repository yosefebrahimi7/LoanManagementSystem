<?php

namespace App\Listeners;

use App\Events\LoanFullyPaid;
use App\Models\User;
use App\Notifications\LoanFullyPaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLoanFullyPaidNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(LoanFullyPaid $event): void
    {
        $loan = $event->loan;
        
        // Send notification to user
        $loan->user->notify(new LoanFullyPaidNotification($loan));
        
        // Send notification to all admins
        $admins = User::where('role', User::ROLE_ADMIN)->get();
        foreach ($admins as $admin) {
            $admin->notify(new LoanFullyPaidNotification($loan));
        }
    }
}

