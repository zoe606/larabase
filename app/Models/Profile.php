<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $bio
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property Gender|null $gender
 * @property string $timezone
 * @property string $locale
 * @property string|null $website
 * @property string|null $twitter
 * @property string|null $linkedin
 * @property string|null $github
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 */
class Profile extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'bio',
        'date_of_birth',
        'gender',
        'timezone',
        'locale',
        'website',
        'twitter',
        'linkedin',
        'github',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => Gender::class,
        ];
    }

    /**
     * Register the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Register the media conversions for the model.
     * Conversions are skipped if GD extension is not available.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Skip conversions if GD extension is not available
        if (! extension_loaded('gd')) {
            return;
        }

        $this->addMediaConversion('thumbnail')
            ->performOnCollections('avatar')
            ->width(150)
            ->height(150)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->performOnCollections('avatar')
            ->width(300)
            ->height(300)
            ->sharpen(10);
    }

    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the avatar URL (thumbnail conversion, fallback to original).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('avatar', 'thumbnail');

        // Fallback to original if conversion doesn't exist
        if (! $url) {
            $url = $this->getFirstMediaUrl('avatar');
        }

        return $url ?: null;
    }

    /**
     * Get the avatar URL (medium conversion, fallback to original).
     */
    public function getAvatarMediumUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('avatar', 'medium');

        // Fallback to original if conversion doesn't exist
        if (! $url) {
            $url = $this->getFirstMediaUrl('avatar');
        }

        return $url ?: null;
    }

    /**
     * Get the avatar URL (original).
     */
    public function getAvatarOriginalUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: null;
    }
}
