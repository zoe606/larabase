<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Profile|null $profile
 */
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the profile associated with the user.
     *
     * @return HasOne<Profile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's display name (from profile or fallback to users table).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->profile->name ?? $this->name;
    }

    /**
     * Get the user's avatar URL (from profile).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->profile?->avatar_url;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<MediaFolder, $this>
     */
    public function mediaFolders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MediaFolder::class);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Automatically create profile when user is created
        static::created(function (User $user): void {
            if (! $user->profile) {
                $user->profile()->create([
                    'name' => $user->name,
                ]);
            }
        });
    }
}
