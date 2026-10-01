<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Profile>
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'bio' => fake()->paragraph(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id',
            'website' => fake()->optional()->url(),
            'twitter' => fake()->optional()->userName(),
            'linkedin' => fake()->optional()->userName(),
            'github' => fake()->optional()->userName(),
        ];
    }

    /**
     * Indicate that the profile has minimal information.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => null,
            'address' => null,
            'bio' => null,
            'date_of_birth' => null,
            'gender' => null,
            'website' => null,
            'twitter' => null,
            'linkedin' => null,
            'github' => null,
        ]);
    }

    /**
     * Indicate that the profile belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'name' => $user->name,
        ]);
    }
}
