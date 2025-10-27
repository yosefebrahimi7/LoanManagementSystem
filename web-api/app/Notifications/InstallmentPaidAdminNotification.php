<?php

namespace App\Notifications;

use App\Models\LoanSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstallmentPaidAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $installment;

    /**
     * Create a new notification instance.
     */
    public function __construct(LoanSchedule $installment)
    {
        $this->installment = $installment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $loan = $this->installment->loan;
        
        return [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'user_name' => $loan->user->first_name . ' ' . $loan->user->last_name,
            'installment_number' => $this->installment->installment_number,
            'amount_paid' => $this->installment->paid_amount,
            'paid_at' => $this->installment->paid_at,
            'message' => 'یک قسط پرداخت شد',
            'type' => 'installment_paid',
            'timestamp' => now()->toISOString(),
        ];
    }
}

