<?php

declare(strict_types=1);

namespace App\Queries\User;

use App\Contracts\QueryInterface;
use App\Models\User;
use App\Queries\Concerns\Paginatable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ListUsersQuery implements QueryInterface
{
    use Paginatable;

    /**
     * List users with optional filters.
     *
     * @param  array{search?: string|null, role?: string|null, paginate?: bool, per_page?: int}  $filters
     * @return LengthAwarePaginator<User>|Collection<int, User>
     */
    public function handle(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = User::query()
            ->with(['roles', 'profile'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(
                fn ($q) => $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhereHas('profile', fn ($pq) => $pq->where('name', 'like', '%'.$search.'%'))
            ))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->role($role))
            ->latest();

        return $this->paginateOrGet($query, $filters);
    }
}
