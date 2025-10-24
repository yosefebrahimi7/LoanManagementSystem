<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
            ->subject('خوش آمدید - ثبت نام موفق')
            ->greeting('سلام ' . $this->user->first_name . '!')
            ->line('ثبت نام شما با موفقیت انجام شد.')
            ->line('شما می‌توانید از تمام امکانات سیستم استفاده کنید.')
            ->action('ورود به سیستم', url('/login'))
            ->line('با تشکر از شما!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'message' => 'ثبت نام با موفقیت انجام شد',
            'type' => 'registration_success',
            'timestamp' => now()->toISOString(),
        ];
    }
}