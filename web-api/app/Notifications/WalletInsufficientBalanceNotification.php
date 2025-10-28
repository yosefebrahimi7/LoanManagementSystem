<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class WalletInsufficientBalanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $data
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('موجودی کیف پول ناکافی')
            ->line($this->data['message'] ?? 'موجودی کیف پول شما کافی نیست.')
            ->line('موجودی فعلی: ' . number_format($this->data['current_balance'] ?? 0, 0) . ' تومان')
            ->line('مبلغ مورد نیاز: ' . number_format($this->data['required_amount'] ?? 0, 0) . ' تومان')
            ->action('شارژ کیف پول', route('wallet.recharge'))
            ->line('لطفاً برای ادامه، کیف پول خود را شارژ کنید.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'wallet_insufficient',
            'message' => $this->data['message'] ?? 'موجودی کیف پول شما کافی نیست.',
            'current_balance' => $this->data['current_balance'] ?? 0,
            'required_amount' => $this->data['required_amount'] ?? 0,
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }
}

