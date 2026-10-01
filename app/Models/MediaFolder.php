<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Builder<MediaFolder> forUser(int|User $user)
 * @method static Builder<MediaFolder> root()
 * @method static Builder<MediaFolder> withSearch(?string $search)
 */
class MediaFolder extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFolderFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'parent_id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<MediaFolder, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    /**
     * Recursive children loading - use only when full tree is needed
     *
     * @return HasMany<MediaFolder, $this>
     */
    public function recursiveChildren(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id')
            ->with('recursiveChildren');
    }

    /**
     * @return BelongsTo<MediaFolder, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    /**
     * Scope: filter folders by user
     *
     * @param  Builder<MediaFolder>  $query
     * @return Builder<MediaFolder>
     */
    public function scopeForUser(Builder $query, int|User $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Scope: only root folders (no parent)
     *
     * @param  Builder<MediaFolder>  $query
     * @return Builder<MediaFolder>
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
     * @param  Builder<MediaFolder>  $query
     * @return Builder<MediaFolder>
     */
    public function scopeWithSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'));
    }
}
