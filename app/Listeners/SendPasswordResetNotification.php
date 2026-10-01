<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserPasswordReset;
use App\Notifications\PasswordResetNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPasswordResetNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     */
    public int $tries = 3;

    /**
     * Handle the event.
     */
    public function handle(UserPasswordReset $event): void
    {
        $event->user->notify(new PasswordResetNotification($event->plainPassword));
    }
}
