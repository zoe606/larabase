<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
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
            ->subject(__('notifications.welcome.subject'))
            ->greeting(__('notifications.welcome.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.welcome.intro'))
            ->action(__('notifications.welcome.action'), url('/dashboard'));

        if ($this->plainPassword) {
            $message->line(__('notifications.welcome.password', ['password' => $this->plainPassword]));
        }

        return $message->line(__('notifications.welcome.outro'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('notifications.welcome.title'),
            'message' => __('notifications.welcome.message'),
            'type' => 'welcome',
        ];
    }
}
