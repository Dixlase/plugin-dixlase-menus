<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc.
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

namespace Plugins\DixlaseMenus\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\DixlaseMenus\App\Models\Menu;
use Plugins\DixlaseMenus\App\Models\MenuItem;
use Tests\TestCase;

class MenuModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => base_path('plugins/DixlaseMenus/database/migrations'),
            '--realpath' => true,
        ]);
    }

    private function createMenu(array $overrides = []): Menu
    {
        return Menu::create(array_merge([
            'name' => 'Test Menu',
            'slug' => 'test-menu',
            'lang' => 'ja',
            'is_active' => true,
            'display_order' => 1,
        ], $overrides));
    }

    private function createMenuItem(Menu $menu, array $overrides = []): MenuItem
    {
        return MenuItem::create(array_merge([
            'menu_id' => $menu->id,
            'title' => 'Test Item',
            'url' => '/test',
            'source_type' => 'custom_url',
            'depth' => 0,
            'display_order' => 1,
            'is_active' => true,
            'is_visible' => true,
        ], $overrides));
    }

    // =========================================================================
    // 基本操作
    // =========================================================================

    public function test_menu_can_be_created(): void
    {
        $menu = $this->createMenu();

        $this->assertDatabaseHas('plg_dixlase_menus', ['slug' => 'test-menu']);
    }

    public function test_is_active_cast_to_boolean(): void
    {
        $menu = $this->createMenu();

        $this->assertIsBool($menu->is_active);
    }

    // =========================================================================
    // リレーション
    // =========================================================================

    public function test_items_relationship(): void
    {
        $menu = $this->createMenu();
        $this->createMenuItem($menu);
        $this->createMenuItem($menu, ['title' => 'Second', 'display_order' => 2]);

        $this->assertCount(2, $menu->items);
    }

    public function test_root_items_excludes_children(): void
    {
        $menu = $this->createMenu();
        $parent = $this->createMenuItem($menu, ['title' => 'Parent']);
        $this->createMenuItem($menu, ['title' => 'Child', 'parent_id' => $parent->id, 'depth' => 1]);

        $this->assertCount(1, $menu->rootItems);
        $this->assertEquals('Parent', $menu->rootItems->first()->title);
    }

    public function test_active_items_excludes_inactive(): void
    {
        $menu = $this->createMenu();
        $this->createMenuItem($menu, ['title' => 'Active', 'is_active' => true]);
        $this->createMenuItem($menu, ['title' => 'Inactive', 'is_active' => false]);

        $this->assertCount(1, $menu->activeItems);
    }

    // =========================================================================
    // スコープ
    // =========================================================================

    public function test_active_scope(): void
    {
        $this->createMenu(['slug' => 'active', 'is_active' => true]);
        $this->createMenu(['slug' => 'inactive', 'is_active' => false]);

        $this->assertEquals(1, Menu::active()->count());
    }

    public function test_by_slug_scope(): void
    {
        $this->createMenu(['slug' => 'header']);
        $this->createMenu(['slug' => 'footer']);

        $menu = Menu::bySlug('header')->first();

        $this->assertNotNull($menu);
        $this->assertEquals('header', $menu->slug);
    }

    public function test_for_lang_scope(): void
    {
        $this->createMenu(['slug' => 'ja-menu', 'lang' => 'ja']);
        $this->createMenu(['slug' => 'en-menu', 'lang' => 'en']);

        $this->assertEquals(1, Menu::forLang('ja')->count());
    }

    // =========================================================================
    // ヘルパーメソッド
    // =========================================================================

    public function test_is_empty(): void
    {
        $menu = $this->createMenu();

        $this->assertTrue($menu->isEmpty());

        $this->createMenuItem($menu);
        $menu->refresh();

        $this->assertFalse($menu->isEmpty());
    }

    public function test_has_hierarchy(): void
    {
        $menu = $this->createMenu();
        $parent = $this->createMenuItem($menu, ['depth' => 0]);

        $this->assertFalse($menu->hasHierarchy());

        $this->createMenuItem($menu, ['parent_id' => $parent->id, 'depth' => 1]);
        $menu->refresh();

        $this->assertTrue($menu->hasHierarchy());
    }

    public function test_items_count_attribute(): void
    {
        $menu = $this->createMenu();
        $this->createMenuItem($menu);
        $this->createMenuItem($menu, ['title' => 'Second', 'display_order' => 2]);

        $this->assertEquals(2, $menu->items_count);
    }
}
