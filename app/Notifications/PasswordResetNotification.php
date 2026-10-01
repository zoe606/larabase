<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly ?string $plainPassword = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('notifications.password_reset.subject'))
            ->greeting(__('notifications.password_reset.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.password_reset.intro'));

        if ($this->plainPassword) {
            $message->line(__('notifications.password_reset.password', ['password' => $this->plainPassword]));
        }

        return $message
            ->action(__('notifications.password_reset.action'), url('/login'))
            ->line(__('notifications.password_reset.outro'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('notifications.password_reset.title'),
            'message' => __('notifications.password_reset.message'),
            'type' => 'password_reset',
        ];
    }
}
