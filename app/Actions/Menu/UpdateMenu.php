<?php

declare(strict_types=1);

namespace App\Actions\Menu;

use App\Contracts\ActionInterface;
use App\Http\Middleware\ShareMenus;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

final class UpdateMenu implements ActionInterface
{
    /**
     * Update an existing menu item.
     *
     * @param  array{id: int, title: string, icon?: string|null, route?: string|null, parent_id?: int|null, permission_name?: string|null, order?: int}  $data
     */
    public function handle(array $data): Menu
    {
        return DB::transaction(function () use ($data): Menu {
            $menu = Menu::findOrFail($data['id']);

            $menu->update([
                'title' => $data['title'],
                'icon' => $data['icon'] ?? null,
                'route' => $data['route'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'permission_name' => $data['permission_name'] ?? null,
                'order' => $data['order'] ?? $menu->order,
            ]);

            // Clear menu cache for all users
            ShareMenus::clearAllCache();

            return $menu->fresh();
        });
    }
}
