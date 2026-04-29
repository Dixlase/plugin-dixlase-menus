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
use Plugins\DixlaseMenus\app\Models\Menu;
use Plugins\DixlaseMenus\app\Models\MenuItem;
use Tests\TestCase;

class MenuItemModelTest extends TestCase
{
    use RefreshDatabase;

    private Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => base_path('plugins/DixlaseMenus/database/migrations'),
            '--realpath' => true,
        ]);

        $this->menu = Menu::create([
            'name' => 'Test Menu',
            'slug' => 'test',
            'lang' => 'ja',
            'is_active' => true,
        ]);
    }

    private function createItem(array $overrides = []): MenuItem
    {
        return MenuItem::create(array_merge([
            'menu_id' => $this->menu->id,
            'title' => 'Test Item',
            'url' => '/test',
            'source_type' => 'custom_url',
            'depth' => 0,
            'display_order' => 1,
            'is_active' => true,
            'is_visible' => true,
        ], $overrides));
    }

    public function test_item_can_be_created(): void
    {
        $item = $this->createItem();

        $this->assertDatabaseHas('plg_dixlase_menu_items', ['title' => 'Test Item']);
    }

    public function test_is_root(): void
    {
        $root = $this->createItem(['parent_id' => null]);
        $child = $this->createItem(['parent_id' => $root->id, 'depth' => 1]);

        $this->assertTrue($root->isRoot());
        $this->assertFalse($child->isRoot());
    }

    public function test_has_children(): void
    {
        $parent = $this->createItem();

        $this->assertFalse($parent->hasChildren());

        $this->createItem(['parent_id' => $parent->id, 'depth' => 1]);

        $this->assertTrue($parent->hasChildren());
    }

    public function test_is_external_link(): void
    {
        $external = $this->createItem(['url' => 'https://example.com']);
        $internal = $this->createItem(['url' => '/about']);

        $this->assertTrue($external->isExternalLink());
        $this->assertFalse($internal->isExternalLink());
    }

    public function test_is_custom_url(): void
    {
        $custom = $this->createItem(['source_type' => 'custom_url']);
        $page = $this->createItem(['source_type' => 'dixlase_pages']);

        $this->assertTrue($custom->isCustomUrl());
        $this->assertFalse($page->isCustomUrl());
    }

    public function test_has_icon(): void
    {
        $withIcon = $this->createItem(['icon_class' => 'fa-home']);
        $withoutIcon = $this->createItem(['icon_class' => null]);

        $this->assertTrue($withIcon->hasIcon());
        $this->assertFalse($withoutIcon->hasIcon());
    }

    public function test_should_display(): void
    {
        $visible = $this->createItem(['is_active' => true, 'is_visible' => true]);
        $inactive = $this->createItem(['is_active' => false, 'is_visible' => true]);
        $hidden = $this->createItem(['is_active' => true, 'is_visible' => false]);

        $this->assertTrue($visible->shouldDisplay());
        $this->assertFalse($inactive->shouldDisplay());
        $this->assertFalse($hidden->shouldDisplay());
    }

    public function test_children_relationship(): void
    {
        $parent = $this->createItem();
        $this->createItem(['parent_id' => $parent->id, 'depth' => 1, 'title' => 'Child 1']);
        $this->createItem(['parent_id' => $parent->id, 'depth' => 1, 'title' => 'Child 2']);

        $this->assertCount(2, $parent->children);
    }

    public function test_menu_relationship(): void
    {
        $item = $this->createItem();

        $this->assertNotNull($item->menu);
        $this->assertEquals($this->menu->id, $item->menu->id);
    }

    public function test_active_scope(): void
    {
        $this->createItem(['is_active' => true, 'is_visible' => true]);
        $this->createItem(['is_active' => false, 'is_visible' => true]);
        $this->createItem(['is_active' => true, 'is_visible' => false]);

        $this->assertEquals(1, MenuItem::active()->count());
    }

    public function test_root_scope(): void
    {
        $parent = $this->createItem(['parent_id' => null]);
        $this->createItem(['parent_id' => $parent->id, 'depth' => 1]);

        $this->assertEquals(1, MenuItem::root()->count());
    }

    public function test_get_full_path(): void
    {
        $parent = $this->createItem(['title' => 'Parent']);
        $child = $this->createItem([
            'title' => 'Child',
            'parent_id' => $parent->id,
            'depth' => 1,
        ]);

        $path = $child->getFullPath();

        $this->assertStringContainsString('Parent', $path);
        $this->assertStringContainsString('Child', $path);
    }

    public function test_label_attribute(): void
    {
        $item = $this->createItem(['title' => 'My Link']);

        $this->assertEquals('My Link', $item->label);
    }
}
