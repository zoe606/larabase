<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @method static Builder<Menu> root()
 * @method static Builder<Menu> forUser(\App\Models\User $user)
 * @method static Builder<Menu> forPermissions(array<string>|Collection<int, string> $permissionNames)
 * @method static Builder<Menu> withSearch(?string $search)
 * @method static Builder<Menu> ordered()
 */
class Menu extends Model
{
    /** @use HasFactory<\Database\Factories\MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'icon',
        'route',
        'parent_id',
        'order',
        'permission_name',
    ];

    /**
     * Relasi menu anak (nested menu) - WITHOUT recursive eager loading
     * Use explicit ->with('children') in queries when needed
     *
     * @return HasMany<Menu, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->orderBy('order');
    }

    /**
     * Recursive children loading - use only when full tree is needed
     *
     * @return HasMany<Menu, $this>
     */
    public function recursiveChildren(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->with('recursiveChildren')
            ->orderBy('order');
    }

    /**
     * Relasi menu induk (jika nested)
     *
     * @return BelongsTo<Menu, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Scope: hanya menu root (tanpa parent)
     *
     * @param  Builder<Menu>  $query
     * @return Builder<Menu>
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: filter by search term
     * Note: Laravel's query builder uses parameterized queries automatically,
     * so the LIKE pattern is bound safely and SQL injection is prevented.
     *
     * @param  Builder<Menu>  $query
     * @return Builder<Menu>
     */
    public function scopeWithSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn ($q) => $q->where('title', 'like', '%'.$search.'%'));
    }

    /**
     * Scope: order by order column
     *
     * @param  Builder<Menu>  $query
     * @return Builder<Menu>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /**
     * Scope: menu yang dapat diakses oleh user (berdasarkan permission)
     * Optimized: pre-loads permissions to avoid N+1
     *
     * @param  Builder<Menu>  $query
     * @return Builder<Menu>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        // Pre-load permissions once, outside the query builder
        $permissionNames = $user->getAllPermissions()->pluck('name')->toArray();

        return $query->forPermissions($permissionNames);
    }

    /**
     * Scope: filter menus by permission names (optimized for reuse)
     *
     * @param  Builder<Menu>  $query
     * @param  array<string>|Collection<int, string>  $permissionNames
     * @return Builder<Menu>
     */
    public function scopeForPermissions(Builder $query, array|Collection $permissionNames): Builder
    {
        $names = $permissionNames instanceof Collection ? $permissionNames->toArray() : $permissionNames;

        return $query->where(function ($q) use ($names) {
            $q->whereNull('permission_name')
                ->orWhereIn('permission_name', $names);
        });
    }
}
