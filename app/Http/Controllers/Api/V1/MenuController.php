<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Menu\CreateMenu;
use App\Actions\Menu\DeleteMenu;
use App\Actions\Menu\ReorderMenus;
use App\Actions\Menu\UpdateMenu;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\ReorderMenuRequest;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Http\Responses\ApiResponse;
use App\Models\Menu;
use App\Queries\Menu\GetMenuTreeQuery;
use App\Queries\Menu\ListMenusQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private readonly GetMenuTreeQuery $getMenuTree,
        private readonly ListMenusQuery $listMenus,
        private readonly CreateMenu $createMenu,
        private readonly UpdateMenu $updateMenu,
        private readonly DeleteMenu $deleteMenu,
        private readonly ReorderMenus $reorderMenus,
    ) {}

    /**
     * Display a listing of menus.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Menu::class);

        if ($request->boolean('tree')) {
            $menus = $this->getMenuTree->handle([]);

            return ApiResponse::success(
                MenuResource::collection($menus)
            );
        }

        $menus = $this->listMenus->handle([
            'search' => $request->search,
            'root_only' => $request->boolean('root_only'),
            'per_page' => $request->per_page ?? 15,
        ]);

        return ApiResponse::paginated(
            MenuResource::collection($menus)
        );
    }

    /**
     * Store a newly created menu.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = $this->createMenu->handle($request->validated());

        return ApiResponse::created(
            new MenuResource($menu),
            __('menus.created')
        );
    }

    /**
     * Display the specified menu.
     */
    public function show(Menu $menu): JsonResponse
    {
        $this->authorize('view', $menu);

        return ApiResponse::success(
            new MenuResource($menu->load('children'))
        );
    }

    /**
     * Update the specified menu.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $menu = $this->updateMenu->handle([...$request->validated(), 'id' => $menu->id]);

        return ApiResponse::success(
            new MenuResource($menu),
            __('menus.updated')
        );
    }

    /**
     * Remove the specified menu.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $this->authorize('delete', $menu);

        $this->deleteMenu->handle(['id' => $menu->id]);

        return ApiResponse::success(null, __('menus.deleted'));
    }

    /**
     * Reorder menus.
     */
    public function reorder(ReorderMenuRequest $request): JsonResponse
    {
        $this->reorderMenus->handle($request->validated());

        return ApiResponse::success(null, __('menus.reordered'));
    }
}
