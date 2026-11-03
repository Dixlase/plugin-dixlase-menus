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

namespace Plugins\DixlaseMenus\Tests\Unit\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Plugins\DixlaseMenus\App\Http\Requests\AdminMenuItemStoreRequest;
use Plugins\DixlaseMenus\App\Http\Requests\AdminMenuItemUpdateRequest;
use Plugins\DixlaseMenus\App\Models\Menu;
use Plugins\DixlaseMenus\App\Models\MenuItem;
use Tests\TestCase;

/**
 * The parent_id rule has to reach the table the model actually uses.
 *
 * Both requests validated it with `exists:menu_items,id`. The table is
 * plg_dixlase_menu_items and the connection adds its own prefix, so the
 * validator queried a table that does not exist: saving an item with a
 * parent threw, the administrator got a 500 and the input error turned
 * into a system-error e-mail (#62). Resolving the table through the model
 * keeps the name and the prefix in one place.
 *
 * The existing smoke test cannot catch this: it only issues GET requests,
 * and the rule runs on POST.
 */
class MenuItemParentRuleTest extends TestCase
{
    use RefreshDatabase;

    private MenuItem $parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => base_path('plugins/DixlaseMenus/database/migrations'),
            '--realpath' => true,
        ]);

        $menu = Menu::create([
            'name' => 'Test Menu',
            'slug' => 'test',
            'lang' => 'ja',
            'is_active' => true,
        ]);

        $this->parent = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Parent',
            'source_type' => 'custom',
            'url' => '/parent',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function requests(): array
    {
        return [
            'store' => [AdminMenuItemStoreRequest::class],
            'update' => [AdminMenuItemUpdateRequest::class],
        ];
    }

    /**
     * @param  class-string  $requestClass
     */
    private function parentRule(string $requestClass): array
    {
        $rules = (new $requestClass())->rules();

        $this->assertArrayHasKey('parent_id', $rules, $requestClass);

        return ['parent_id' => $rules['parent_id']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('requests')]
    public function test_an_existing_parent_passes(string $requestClass): void
    {
        $validator = Validator::make(
            ['parent_id' => $this->parent->id],
            $this->parentRule($requestClass)
        );

        // Before the fix this threw, because the queried table did not
        // exist — which is what the administrator saw as a 500.
        $this->assertFalse($validator->fails(), $requestClass.': '.$validator->errors()->first('parent_id'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('requests')]
    public function test_an_unknown_parent_is_refused_as_a_validation_error(string $requestClass): void
    {
        $validator = Validator::make(
            ['parent_id' => $this->parent->id + 1000],
            $this->parentRule($requestClass)
        );

        $this->assertTrue($validator->fails(), $requestClass);
        $this->assertTrue($validator->errors()->has('parent_id'), $requestClass);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('requests')]
    public function test_no_parent_is_allowed(string $requestClass): void
    {
        $validator = Validator::make(
            ['parent_id' => null],
            $this->parentRule($requestClass)
        );

        $this->assertFalse($validator->fails(), $requestClass);
    }
}
