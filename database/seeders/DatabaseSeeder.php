<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MenuSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $user = User::firstOrCreate(
                ['email' => 'admin@admin.com'],
                ['name' => 'Admin', 'password' => Hash::make('admin123')]
            );

            if (! $user->hasRole('admin')) {
                $user->assignRole('admin');
            }
        }
    }
}
