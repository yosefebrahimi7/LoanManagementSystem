<?php

namespace App\Jobs;

use App\Models\LoanSchedule;
use App\Notifications\PaymentConfirmationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $installment;

    /**
     * Create a new job instance.
     */
    public function __construct(LoanSchedule $installment)
    {
        $this->installment = $installment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send payment confirmation notification to user
        $this->installment->loan->user->notify(new PaymentConfirmationNotification($this->installment));
    }
}
