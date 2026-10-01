<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    // Users
    case USERS_VIEW = 'users-view';
    case USERS_CREATE = 'users-create';
    case USERS_EDIT = 'users-edit';
    case USERS_DELETE = 'users-delete';

    // Roles
    case ROLES_VIEW = 'roles-view';
    case ROLES_CREATE = 'roles-create';
    case ROLES_EDIT = 'roles-edit';
    case ROLES_DELETE = 'roles-delete';

    // Permissions
    case PERMISSIONS_VIEW = 'permissions-view';
    case PERMISSIONS_CREATE = 'permissions-create';
    case PERMISSIONS_EDIT = 'permissions-edit';
    case PERMISSIONS_DELETE = 'permissions-delete';

    // Menus
    case MENUS_VIEW = 'menus-view';
    case MENUS_CREATE = 'menus-create';
    case MENUS_EDIT = 'menus-edit';
    case MENUS_DELETE = 'menus-delete';

    // Settings
    case SETTINGS_VIEW = 'settings-view';
    case SETTINGS_EDIT = 'settings-edit';

    // Audit Logs
    case AUDIT_VIEW = 'audit-view';

    // Backups
    case BACKUPS_VIEW = 'backups-view';
    case BACKUPS_CREATE = 'backups-create';
    case BACKUPS_DELETE = 'backups-delete';

    public function label(): string
    {
        return match ($this) {
            self::USERS_VIEW => 'View Users',
            self::USERS_CREATE => 'Create Users',
            self::USERS_EDIT => 'Edit Users',
            self::USERS_DELETE => 'Delete Users',
            self::ROLES_VIEW => 'View Roles',
            self::ROLES_CREATE => 'Create Roles',
            self::ROLES_EDIT => 'Edit Roles',
            self::ROLES_DELETE => 'Delete Roles',
            self::PERMISSIONS_VIEW => 'View Permissions',
            self::PERMISSIONS_CREATE => 'Create Permissions',
            self::PERMISSIONS_EDIT => 'Edit Permissions',
            self::PERMISSIONS_DELETE => 'Delete Permissions',
            self::MENUS_VIEW => 'View Menus',
            self::MENUS_CREATE => 'Create Menus',
            self::MENUS_EDIT => 'Edit Menus',
            self::MENUS_DELETE => 'Delete Menus',
            self::SETTINGS_VIEW => 'View Settings',
            self::SETTINGS_EDIT => 'Edit Settings',
            self::AUDIT_VIEW => 'View Audit Logs',
            self::BACKUPS_VIEW => 'View Backups',
            self::BACKUPS_CREATE => 'Create Backups',
            self::BACKUPS_DELETE => 'Delete Backups',
        };
    }

    public function group(): string
    {
        return match (true) {
            str_starts_with($this->value, 'users-') => 'users',
            str_starts_with($this->value, 'roles-') => 'roles',
            str_starts_with($this->value, 'permissions-') => 'permissions',
            str_starts_with($this->value, 'menus-') => 'menus',
            str_starts_with($this->value, 'settings-') => 'settings',
            str_starts_with($this->value, 'audit-') => 'audit',
            str_starts_with($this->value, 'backups-') => 'backups',
            default => 'other',
        };
    }

    /**
     * Get all permissions for a specific group.
     *
     * @return array<self>
     */
    public static function forGroup(string $group): array
    {
        return array_filter(
            self::cases(),
            fn (self $permission): bool => $permission->group() === $group
        );
    }

    /**
     * Get all permission values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get permissions grouped by their group.
     *
     * @return array<string, array<self>>
     */
    public static function grouped(): array
    {
        $grouped = [];
        foreach (self::cases() as $permission) {
            $grouped[$permission->group()][] = $permission;
        }

        return $grouped;
    }
}
