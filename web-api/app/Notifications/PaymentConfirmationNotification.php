<?php

namespace App\Notifications;

use App\Models\LoanSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmationNotification extends Notification implements ShouldQueue
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Payment Confirmation - Loan Management System')
                    ->greeting('Payment Received!')
                    ->line('Your payment has been successfully processed.')
                    ->line("Installment #{$this->installment->installment_number}")
                    ->line("Amount Paid: " . number_format($this->installment->amount_due / 100, 2) . " IRR")
                    ->line("Principal: " . number_format($this->installment->principal_amount / 100, 2) . " IRR")
                    ->line("Interest: " . number_format($this->installment->interest_amount / 100, 2) . " IRR")
                    ->line("Payment Date: " . $this->installment->paid_at->format('Y-m-d H:i:s'))
                    ->action('View Loan Details', url('/loans/' . $this->installment->loan_id))
                    ->line('Thank you for your payment!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->installment->loan_id,
            'installment_number' => $this->installment->installment_number,
            'amount_due' => $this->installment->amount_due,
            'principal_amount' => $this->installment->principal_amount,
            'interest_amount' => $this->installment->interest_amount,
            'paid_at' => $this->installment->paid_at,
            'message' => 'پرداخت شما با موفقیت انجام شد',
            'type' => 'payment_confirmed',
        ];
    }
}
