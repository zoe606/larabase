<?php

use App\Models\Menu;
use App\Queries\Menu\ListMenusQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new ListMenusQuery;
});

it('returns paginated menus by default', function () {
    Menu::factory()->count(5)->create();

    $result = $this->query->handle();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(5);
});

it('returns collection when paginate is false', function () {
    Menu::factory()->count(5)->create();

    $result = $this->query->handle(['paginate' => false]);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(5);
});

it('loads children relationship when with_children is true', function () {
    $parent = Menu::factory()->create();
    Menu::factory()->create(['parent_id' => $parent->id]);

    $result = $this->query->handle(['paginate' => false, 'with_children' => true]);

    expect($result->first()->relationLoaded('children'))->toBeTrue();
});

it('does not load children relationship by default', function () {
    $parent = Menu::factory()->create();
    Menu::factory()->create(['parent_id' => $parent->id]);

    $result = $this->query->handle(['paginate' => false]);

    expect($result->first()->relationLoaded('children'))->toBeFalse();
});

it('filters by search term', function () {
    Menu::factory()->create(['title' => 'Dashboard']);
    Menu::factory()->create(['title' => 'Settings']);

    $result = $this->query->handle(['search' => 'Dashboard']);

    expect($result->total())->toBe(1)
        ->and($result->first()->title)->toBe('Dashboard');
});

it('filters root only menus', function () {
    $root = Menu::factory()->create(['parent_id' => null]);
    Menu::factory()->create(['parent_id' => $root->id]);

    $result = $this->query->handle(['root_only' => true]);

    expect($result->total())->toBe(1)
        ->and($result->first()->parent_id)->toBeNull();
});

it('orders by order column', function () {
    Menu::factory()->create(['order' => 2]);
    Menu::factory()->create(['order' => 1]);

    $result = $this->query->handle(['paginate' => false]);

    expect($result->first()->order)->toBe(1);
});

it('respects per_page parameter', function () {
    Menu::factory()->count(20)->create();

    $result = $this->query->handle(['per_page' => 5]);

    expect($result->perPage())->toBe(5);
});
