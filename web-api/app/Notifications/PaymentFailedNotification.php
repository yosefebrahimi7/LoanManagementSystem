<?php

namespace App\Notifications;

use App\Models\LoanPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(LoanPayment $payment)
    {
        $this->payment = $payment;
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
        $schedule = $this->payment->schedule;
        $loan = $this->payment->loan;

        return (new MailMessage)
            ->subject('پرداخت ناموفق - سیستم مدیریت وام')
            ->greeting('سلام ' . $notifiable->first_name . '!')
            ->line('متاسفانه پرداخت شما با خطا مواجه شد.')
            ->line("شماره وام: {$loan->id}")
            ->line("قسط: #{$schedule->installment_number}")
            ->line("مبلغ: " . number_format($this->payment->amount / 100, 2) . " ریال")
            ->line("دلیل: " . ($this->payment->notes ?? 'خطای نامشخص'))
            ->action('تلاش مجدد برای پرداخت', url('/loans/' . $loan->id))
            ->line('لطفا مجددا تلاش کنید یا با پشتیبانی تماس بگیرید.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'loan_id' => $this->payment->loan_id,
            'amount' => $this->payment->amount,
            'notes' => $this->payment->notes,
            'message' => 'پرداخت شما ناموفق بود',
            'type' => 'payment_failed',
            'timestamp' => now()->toISOString(),
        ];
    }
}

