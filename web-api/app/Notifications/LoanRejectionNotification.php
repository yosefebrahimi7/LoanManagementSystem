<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanRejectionNotification extends Notification implements ShouldQueue
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
                    ->subject('Loan Application Update - Loan Management System')
                    ->greeting('Dear ' . $notifiable->first_name . ',')
                    ->line('We regret to inform you that your loan application has been rejected.')
                    ->line("Loan Amount: " . number_format($this->loan->amount / 100, 2) . " IRR")
                    ->line("Reason: {$this->loan->rejection_reason}")
                    ->line('You may apply for a new loan after addressing the mentioned concerns.')
                    ->action('Apply for New Loan', url('/loans/create'))
                    ->line('Thank you for using our loan management system!');
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
            'rejection_reason' => $this->loan->rejection_reason,
            'message' => 'درخواست وام شما رد شد',
            'type' => 'loan_rejected',
        ];
    }
}
