<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanFullyPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $loan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
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
            ->subject('وام شما به طور کامل پرداخت شد')
            ->greeting('تبریک!')
            ->line('وام شما با موفقیت تسویه شد.')
            ->line("شماره وام: {$this->loan->id}")
            ->line("مبلغ کل وام: " . number_format($this->loan->amount / 100, 2) . " ریال")
            ->line("تعداد اقساط: {$this->loan->term_months} ماه")
            ->line('با تشکر از حسن انتخاب شما!')
            ->action('مشاهده جزئیات وام', url('/loans/' . $this->loan->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'amount' => $this->loan->amount,
            'message' => 'وام شما به طور کامل پرداخت شد',
            'type' => 'loan_fully_paid',
            'timestamp' => now()->toISOString(),
        ];
    }
}

