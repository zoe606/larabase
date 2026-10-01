<?php

declare(strict_types=1);

namespace App\Actions\Menu;

use App\Contracts\ActionInterface;
use App\Http\Middleware\ShareMenus;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

final class DeleteMenu implements ActionInterface
{
    /**
     * Delete a menu item.
     *
     * @param  array{id: int}  $data
     */
    public function handle(array $data): bool
    {
        return DB::transaction(function () use ($data): bool {
            $menu = Menu::findOrFail($data['id']);

            // Move children to parent level before deletion
            Menu::where('parent_id', $menu->id)->update([
                'parent_id' => $menu->parent_id,
            ]);

            $result = (bool) $menu->delete();

            // Clear menu cache for all users
            ShareMenus::clearAllCache();

            return $result;
        });
    }
}
