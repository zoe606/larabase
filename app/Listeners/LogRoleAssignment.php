<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\RoleAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogRoleAssignment implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(RoleAssigned $event): void
    {
        Log::info('Role assignment changed', [
            'user_id' => $event->user->id,
            'user_email' => $event->user->email,
            'new_roles' => $event->roles,
            'previous_roles' => $event->previousRoles,
            'changed_by' => auth()->id(),
        ]);
    }
}
