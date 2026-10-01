<?php

use App\Actions\Menu\ReorderMenus;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new ReorderMenus;
});

it('reorders root level menus', function () {
    $menu1 = Menu::factory()->create(['order' => 0, 'parent_id' => null]);
    $menu2 = Menu::factory()->create(['order' => 1, 'parent_id' => null]);
    $menu3 = Menu::factory()->create(['order' => 2, 'parent_id' => null]);

    $data = [
        'menus' => [
            ['id' => $menu3->id],
            ['id' => $menu1->id],
            ['id' => $menu2->id],
        ],
    ];

    $result = $this->action->handle($data);

    expect($result)->toBeTrue();

    $menu1->refresh();
    $menu2->refresh();
    $menu3->refresh();

    expect($menu3->order)->toBe(0)
        ->and($menu1->order)->toBe(1)
        ->and($menu2->order)->toBe(2);
});

it('handles nested structure', function () {
    $parent = Menu::factory()->create(['order' => 0, 'parent_id' => null]);
    $child1 = Menu::factory()->create(['order' => 0, 'parent_id' => null]);
    $child2 = Menu::factory()->create(['order' => 1, 'parent_id' => null]);

    $data = [
        'menus' => [
            [
                'id' => $parent->id,
                'children' => [
                    ['id' => $child2->id],
                    ['id' => $child1->id],
                ],
            ],
        ],
    ];

    $this->action->handle($data);

    $child1->refresh();
    $child2->refresh();

    expect($child1->parent_id)->toBe($parent->id)
        ->and($child2->parent_id)->toBe($parent->id)
        ->and($child2->order)->toBe(0)
        ->and($child1->order)->toBe(1);
});

it('moves menu from child to root', function () {
    $parent = Menu::factory()->create(['parent_id' => null]);
    $child = Menu::factory()->create(['parent_id' => $parent->id]);

    $data = [
        'menus' => [
            ['id' => $parent->id],
            ['id' => $child->id],
        ],
    ];

    $this->action->handle($data);

    $child->refresh();

    expect($child->parent_id)->toBeNull();
});
