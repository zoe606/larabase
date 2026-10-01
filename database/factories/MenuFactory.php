<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(2, true),
            'icon' => 'icon-'.fake()->word(),
            'route' => fake()->slug(),
            'parent_id' => null,
            'order' => fake()->numberBetween(0, 100),
            'permission_name' => null,
        ];
    }

    /**
     * Indicate that the menu is a child of another menu.
     */
    public function child(Menu $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Indicate that the menu has a permission.
     */
    public function withPermission(string $permission): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_name' => $permission,
        ]);
    }
}
