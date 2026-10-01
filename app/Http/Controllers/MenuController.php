<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Menu\CreateMenu;
use App\Actions\Menu\DeleteMenu;
use App\Actions\Menu\ReorderMenus;
use App\Actions\Menu\UpdateMenu;
use App\Http\Requests\Menu\ReorderMenuRequest;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Queries\Menu\GetMenuTreeQuery;
use App\Queries\Menu\ListMenusQuery;
use App\Queries\Permission\ListPermissionsQuery;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        private readonly GetMenuTreeQuery $getMenuTree,
        private readonly ListMenusQuery $listMenus,
        private readonly ListPermissionsQuery $listPermissions,
        private readonly CreateMenu $createMenu,
        private readonly UpdateMenu $updateMenu,
        private readonly DeleteMenu $deleteMenu,
        private readonly ReorderMenus $reorderMenus,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Menu::class);

        $menus = $this->getMenuTree->handle([]);

        return Inertia::render('menus/Index', [
            'menuItems' => $menus,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Menu::class);

        $menus = $this->listMenus->handle(['paginate' => false]);
        $permissions = $this->listPermissions->handle(['paginate' => false])
            ->pluck('name');

        return Inertia::render('menus/Form', [
            'parentMenus' => $menus,
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $this->createMenu->handle($request->validated());

        return redirect()->route('menus.index')
            ->with('success', __('menus.created'));
    }

    public function edit(Menu $menu): Response
    {
        $this->authorize('update', $menu);

        $menus = $this->listMenus->handle(['paginate' => false])
            ->where('id', '!=', $menu->id)
            ->values();
        $permissions = $this->listPermissions->handle(['paginate' => false])
            ->pluck('name');

        return Inertia::render('menus/Form', [
            'menu' => $menu,
            'parentMenus' => $menus,
            'permissions' => $permissions,
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->updateMenu->handle([...$request->validated(), 'id' => $menu->id]);

        return redirect()->route('menus.index')
            ->with('success', __('menus.updated'));
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('delete', $menu);

        $this->deleteMenu->handle(['id' => $menu->id]);

        return redirect()->route('menus.index')
            ->with('success', __('menus.deleted'));
    }

    public function reorder(ReorderMenuRequest $request): RedirectResponse
    {
        $this->reorderMenus->handle($request->validated());

        return redirect()->back()
            ->with('success', __('menus.reordered'));
    }
}
