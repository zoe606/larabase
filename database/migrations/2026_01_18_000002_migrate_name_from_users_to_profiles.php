<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create profiles for all existing users
        DB::transaction(function (): void {
            User::query()
                ->whereDoesntHave('profile')
                ->each(function (User $user): void {
                    DB::table('profiles')->insert([
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'timezone' => 'Asia/Jakarta',
                        'locale' => 'id',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        });

        // Note: We keep the 'name' column on users for backward compatibility
        // It can be removed in a future migration once all code is updated
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sync names back to users table if needed
        DB::transaction(function (): void {
            DB::table('profiles')
                ->select(['user_id', 'name'])
                ->each(function ($profile): void {
                    DB::table('users')
                        ->where('id', $profile->user_id)
                        ->update(['name' => $profile->name]);
                });

            // Delete all profiles
            DB::table('profiles')->truncate();
        });
    }
};
