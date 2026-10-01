<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds performance indexes for frequently queried columns:
     * - menus.permission_name: Used in forPermissions scope with whereIn()
     * - menus.order: Used for sorting in most menu queries
     * - menus.parent_id: Used for tree queries (already has FK, explicit index)
     * - permissions.group: Used for grouping and filtering
     * - media_folders.user_id: Used for filtering folders by user
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->index('permission_name', 'menus_permission_name_index');
            $table->index('order', 'menus_order_index');
            $table->index('parent_id', 'menus_parent_id_index');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->index('group', 'permissions_group_index');
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->index('user_id', 'media_folders_user_id_index');
            $table->index('parent_id', 'media_folders_parent_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_permission_name_index');
            $table->dropIndex('menus_order_index');
            $table->dropIndex('menus_parent_id_index');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex('permissions_group_index');
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->dropIndex('media_folders_user_id_index');
            $table->dropIndex('media_folders_parent_id_index');
        });
    }
};
