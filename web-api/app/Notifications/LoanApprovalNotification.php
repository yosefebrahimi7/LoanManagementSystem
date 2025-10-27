<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanApprovalNotification extends Notification implements ShouldQueue
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
                    ->subject('Loan Approved - Loan Management System')
                    ->greeting('Congratulations!')
                    ->line('Your loan application has been approved.')
                    ->line("Loan Amount: " . number_format($this->loan->amount / 100, 2) . " IRR")
                    ->line("Monthly Payment: " . number_format($this->loan->monthly_payment / 100, 2) . " IRR")
                    ->line("Term: {$this->loan->term_months} months")
                    ->line("Interest Rate: {$this->loan->interest_rate}%")
                    ->action('View Loan Details', url('/loans/' . $this->loan->id))
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
            'monthly_payment' => $this->loan->monthly_payment,
            'term_months' => $this->loan->term_months,
            'interest_rate' => $this->loan->interest_rate,
            'message' => 'درخواست وام شما تایید شد',
            'type' => 'loan_approved',
        ];
    }
}
