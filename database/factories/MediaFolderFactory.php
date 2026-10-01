<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaFolder>
 */
class MediaFolderFactory extends Factory
{
    protected $model = MediaFolder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word().' '.fake()->randomElement(['Documents', 'Photos', 'Projects', 'Archive', 'Work']),
            'parent_id' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function withParent(MediaFolder $parent): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
            'user_id' => $parent->user_id,
        ]);
    }
}
