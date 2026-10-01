<?php

declare(strict_types=1);

namespace App\Actions\Menu;

use App\Contracts\ActionInterface;
use App\Http\Middleware\ShareMenus;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

final class ReorderMenus implements ActionInterface
{
    /**
     * Reorder menu items (supports nested structure).
     *
     * @param  array{menus: array<int, array{id: int, children?: array<int, mixed>}>}  $data
     */
    public function handle(array $data): bool
    {
        return DB::transaction(function () use ($data): bool {
            $this->updateOrder($data['menus']);

            // Clear menu cache for all users
            ShareMenus::clearAllCache();

            return true;
        });
    }

    /**
     * Recursively update menu order.
     *
     * @param  array<int, array{id: int, children?: array<int, mixed>}>  $items
     */
    private function updateOrder(array $items, ?int $parentId = null): void
    {
        foreach ($items as $index => $item) {
            Menu::where('id', $item['id'])->update([
                'parent_id' => $parentId,
                'order' => $index,
            ]);

            if (! empty($item['children'])) {
                $this->updateOrder($item['children'], $item['id']);
            }
        }
    }
}
