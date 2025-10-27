<?php

namespace App\Notifications;

use App\Models\LoanSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DueDateReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $installment;
    protected $isOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(LoanSchedule $installment, bool $isOverdue = false)
    {
        $this->installment = $installment;
        $this->isOverdue = $isOverdue;
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
        $loan = $this->installment->loan;
        $daysUntilDue = now()->diffInDays($this->installment->due_date, false);
        
        $subject = $this->isOverdue 
            ? 'قسط شما سررسید گذشته است!' 
            : 'یادآوری سررسید قسط';
        
        $greeting = $this->isOverdue 
            ? 'یادآوری مهم' 
            : 'سلام ' . $notifiable->first_name . '!';

        $message = $this->isOverdue
            ? "قسط شماره {$this->installment->installment_number} شما سررسید گذشته است. لطفا هرچه سریعتر پرداخت کنید."
            : "قسط شماره {$this->installment->installment_number} تا {$daysUntilDue} روز دیگر موعد پرداخت دارد.";

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($message)
            ->line("شماره وام: {$loan->id}")
            ->line("مبلغ: " . number_format($this->installment->remaining_amount / 100, 2) . " ریال")
            ->line("موعد پرداخت: " . $this->installment->due_date->format('Y/m/d'))
            ->action('پرداخت قسط', url('/loans/' . $loan->id))
            ->line('لطفا در موعد مقرر پرداخت خود را انجام دهید.');
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
            'installment_number' => $this->installment->installment_number,
            'remaining_amount' => $this->installment->remaining_amount,
            'due_date' => $this->installment->due_date,
            'is_overdue' => $this->isOverdue,
            'message' => $this->isOverdue ? 'قسط سررسید گذشته است' : 'موعد پرداخت قسط نزدیک است',
            'type' => $this->isOverdue ? 'installment_overdue' : 'due_date_reminder',
            'timestamp' => now()->toISOString(),
        ];
    }
}

