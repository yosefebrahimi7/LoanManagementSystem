<?php

namespace App\Listeners;

use App\Events\LoanApproved;
use App\Events\LoanRejected;
use App\Events\InstallmentPaid;
use App\Jobs\SendLoanApprovalNotificationJob;
use App\Jobs\SendLoanRejectionNotificationJob;
use App\Jobs\SendPaymentConfirmationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoanEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle loan approved event
     */
    public function handleLoanApproved(LoanApproved $event): void
    {
        // Send notification to user about loan approval
        SendLoanApprovalNotificationJob::dispatch($event->loan);
    }

    /**
     * Handle loan rejected event
     */
    public function handleLoanRejected(LoanRejected $event): void
    {
        // Send notification to user about loan rejection
        SendLoanRejectionNotificationJob::dispatch($event->loan);
    }

    /**
     * Handle installment paid event
     */
    public function handleInstallmentPaid(InstallmentPaid $event): void
    {
        // Send payment confirmation notification
        SendPaymentConfirmationJob::dispatch($event->installment);
    }
}
