<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  User  $user  The user who was assigned roles
     * @param  array<int, string>  $roles  The roles that were assigned
     * @param  array<int, string>  $previousRoles  The roles the user had before
     */
    public function __construct(
        public readonly User $user,
        public readonly array $roles,
        public readonly array $previousRoles = [],
    ) {}
}
