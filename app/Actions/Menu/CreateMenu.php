<?php

declare(strict_types=1);

namespace App\Actions\Menu;

use App\Contracts\ActionInterface;
use App\Http\Middleware\ShareMenus;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

final class CreateMenu implements ActionInterface
{
    /**
     * Create a new menu item.
     *
     * @param  array{title: string, icon?: string|null, route?: string|null, parent_id?: int|null, permission_name?: string|null, order?: int}  $data
     */
    public function handle(array $data): Menu
    {
        return DB::transaction(function () use ($data): Menu {
            $menu = $this->createMenu($data);

            // Clear menu cache for all users
            ShareMenus::clearAllCache();

            return $menu;
        });
    }

    /**
     * @param  array{title: string, icon?: string|null, route?: string|null, parent_id?: int|null, permission_name?: string|null, order?: int}  $data
     */
    private function createMenu(array $data): Menu
    {
        return Menu::create([
            'title' => $data['title'],
            'icon' => $data['icon'] ?? null,
            'route' => $data['route'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'permission_name' => $data['permission_name'] ?? null,
            'order' => $data['order'] ?? Menu::where('parent_id', $data['parent_id'] ?? null)->max('order') + 1,
        ]);
    }
}
