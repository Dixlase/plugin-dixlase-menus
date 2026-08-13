<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc. and Dixlase contributors
 * https://exc-d.com
 *
 * Dixlase Menus is dual-licensed. You may use this file under either:
 *
 *   (a) the GNU General Public License version 3 or later, as published
 *       by the Free Software Foundation; or
 *
 *   (b) a commercial license agreement obtained from exc-D inc.
 *
 * Unless you have entered into a commercial license agreement, this
 * file is governed by the GPL terms below.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Plugins\DixlaseMenus\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\DixlaseMenus\App\Models\Menu;
use Plugins\DixlaseMenus\App\Models\MenuItem;
use Plugins\DixlaseMenus\App\Repositories\MenuItemRepository;
use Tests\TestCase;

/**
 * MenuItem declares SoftDeletes and the table carries deleted_at, but the
 * repository called forceDelete() in all three of its delete paths, so
 * removing an item from a menu destroyed the row outright:
 *
 *     delete()                 -> $item->forceDelete()
 *     deleteWithDescendants()  -> forceDelete() on the item and every child
 *     deleteByMenuId()         -> forceDelete() across a whole menu
 *
 * The admin item-delete button reaches the first two. Menu itself was
 * already handled the other way round -- softDelete() plus restore() on
 * MenuRepository -- so the container could be recovered and its contents
 * could not.
 */
class MenuItemSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    private MenuItemRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => base_path('plugins/DixlaseMenus/database/migrations'),
            '--realpath' => true,
        ]);

        $this->repository = app(MenuItemRepository::class);
    }

    private function makeMenu(): Menu
    {
        return Menu::create([
            'name' => 'Primary',
            'slug' => 'primary-'.uniqid(),
            'lang' => 'ja',
            'location' => 'header',
        ]);
    }

    private function makeItem(Menu $menu, string $title, ?int $parentId = null): MenuItem
    {
        return MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'title' => $title,
            'url' => '/'.strtolower($title),
            'depth' => $parentId === null ? 0 : 1,
            'display_order' => 0,
        ]);
    }

    public function test_deleting_an_item_keeps_the_row_and_hides_it(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, 'About');

        $this->repository->delete($item->id);

        $this->assertNull(
            MenuItem::find($item->id),
            'A deleted item must not come back from an ordinary query.'
        );
        $this->assertNotNull(
            MenuItem::withTrashed()->find($item->id),
            'The row must survive so the deletion can be undone.'
        );
    }

    public function test_deleting_a_parent_soft_deletes_its_descendants(): void
    {
        $menu = $this->makeMenu();
        $parent = $this->makeItem($menu, 'Products');
        $child = $this->makeItem($menu, 'Hardware', $parent->id);

        $this->repository->deleteWithDescendants($parent->id);

        foreach ([$parent, $child] as $gone) {
            $this->assertNull(MenuItem::find($gone->id));
            $this->assertNotNull(
                MenuItem::withTrashed()->find($gone->id),
                'Cascading to children must not turn a recoverable delete into a permanent one.'
            );
        }
    }

    public function test_deleting_every_item_of_a_menu_is_recoverable(): void
    {
        $menu = $this->makeMenu();
        $first = $this->makeItem($menu, 'Home');
        $second = $this->makeItem($menu, 'Contact');

        $this->repository->deleteByMenuId($menu->id);

        $this->assertSame(0, MenuItem::where('menu_id', $menu->id)->count());
        $this->assertSame(
            2,
            MenuItem::withTrashed()->where('menu_id', $menu->id)->count(),
            'A menu-wide clear is the most destructive of the three and the one most worth undoing.'
        );

        foreach ([$first, $second] as $item) {
            $this->repository->restore($item->id);
        }

        $this->assertSame(2, MenuItem::where('menu_id', $menu->id)->count());
    }

    public function test_a_restored_item_keeps_its_place_in_the_tree(): void
    {
        $menu = $this->makeMenu();
        $parent = $this->makeItem($menu, 'Services');
        $child = $this->makeItem($menu, 'Consulting', $parent->id);

        $this->repository->deleteWithDescendants($parent->id);
        $this->repository->restore($parent->id);
        $this->repository->restore($child->id);

        $restored = MenuItem::find($child->id);

        $this->assertNotNull($restored);
        $this->assertSame(
            $parent->id,
            $restored->parent_id,
            'Restoring must return the item to where it was, not orphan it.'
        );
    }

    /**
     * Soft delete is only half of it. Something still has to be able to
     * remove a row for good -- retention, a privacy request, an operator
     * clearing a menu they will never use again.
     */
    public function test_force_delete_removes_the_row_for_good(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, 'Legacy');

        $this->repository->delete($item->id);
        $this->repository->forceDelete($item->id);

        $this->assertNull(
            MenuItem::withTrashed()->find($item->id),
            'forceDelete() must drop the row, otherwise nothing can.'
        );
    }
}
