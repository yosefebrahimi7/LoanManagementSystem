<?php

namespace App\Events;

use App\Models\LoanSchedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstallmentPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $installment;

    /**
     * Create a new event instance.
     */
    public function __construct(LoanSchedule $installment)
    {
        $this->installment = $installment;
    }
}
