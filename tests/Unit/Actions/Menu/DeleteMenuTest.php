<?php

use App\Actions\Menu\DeleteMenu;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new DeleteMenu;
});

it('deletes a menu', function () {
    $menu = Menu::factory()->create();

    $result = $this->action->handle(['id' => $menu->id]);

    expect($result)->toBeTrue()
        ->and(Menu::find($menu->id))->toBeNull();
});

it('moves children to parent level before deletion', function () {
    $parent = Menu::factory()->create(['parent_id' => null]);
    $child1 = Menu::factory()->create(['parent_id' => $parent->id]);
    $child2 = Menu::factory()->create(['parent_id' => $parent->id]);

    $this->action->handle(['id' => $parent->id]);

    $child1->refresh();
    $child2->refresh();

    expect($child1->parent_id)->toBeNull()
        ->and($child2->parent_id)->toBeNull();
});

it('moves grandchildren to deleted parent level', function () {
    $grandparent = Menu::factory()->create(['parent_id' => null]);
    $parent = Menu::factory()->create(['parent_id' => $grandparent->id]);
    $child = Menu::factory()->create(['parent_id' => $parent->id]);

    $this->action->handle(['id' => $parent->id]);

    $child->refresh();

    expect($child->parent_id)->toBe($grandparent->id);
});

it('throws exception for non-existent menu', function () {
    $this->action->handle(['id' => 99999]);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
