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

namespace Plugins\DixlaseMenus\Tests\Feature\Http\Controllers\Admin;

use App\Enums\MemberRole;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Plugins\DixlaseMenus\App\Models\Menu;
use Plugins\DixlaseMenus\App\Models\MenuItem;
use Tests\TestCase;

/**
 * Regression test for the "create / edit menu item" admin screens.
 *
 * Both actions in AdminMenuItemController used to call
 * `$this->linkSourceManager->all()` while the service only exposes
 * `getAvailableSources()` and `getAllSources()`. The create and the
 * edit view therefore 500'd on open with
 * `Call to undefined method MenuLinkSourceManager::all()`.
 *
 * This test opens both routes and asserts status < 500 so the regression
 * cannot sit undetected again.
 */
class AdminMenuItemControllerLinkSourceTest extends TestCase
{
    use RefreshDatabase;

    private Member $admin;

    private Menu $menu;

    private MenuItem $menuItem;

    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['INSTALLED'] = 'true';
        $_SERVER['INSTALLED'] = 'true';

        // Pin a known-good 32-byte AES-256-CBC key: the plugin CI workflow
        // injects an APP_KEY whose decoded length is not 32 bytes, which
        // AES-256-CBC refuses with "Unsupported cipher or incorrect key
        // length" the first time actingAs() writes to the session.
        $rawKey = random_bytes(32);
        config([
            'app.key' => 'base64:'.base64_encode($rawKey),
            'app.cipher' => 'AES-256-CBC',
        ]);
        $this->app->instance('encrypter', new \Illuminate\Encryption\Encrypter($rawKey, 'AES-256-CBC'));

        Artisan::call('migrate', [
            '--path' => 'plugins/DixlaseMenus/database/migrations',
            '--realpath' => false,
        ]);

        $this->app->register(\Plugins\DixlaseMenus\App\Providers\DixlaseMenusServiceProvider::class);

        $adminUrl = (string) config('admin.admin_url', 'admin');
        $router = app('router');
        $router->group([
            'prefix' => $adminUrl,
            'middleware' => ['web', 'auth:member'],
        ], function () {
            include base_path('plugins/DixlaseMenus/routes/admin.php');
        });
        $router->getRoutes()->refreshNameLookups();
        $router->getRoutes()->refreshActionLookups();

        $this->admin = Member::factory()->create(['role' => MemberRole::ADMIN]);
        $this->menu = Menu::create([
            'name' => 'Regression Menu',
            'slug' => 'regression-menu',
            'lang' => 'en',
            'location' => 'primary',
            'placement_type' => 'manual',
        ]);
        $this->menuItem = MenuItem::create([
            'menu_id' => $this->menu->id,
            'parent_id' => null,
            'title' => 'Regression item',
            'url' => '/regression',
            'order' => 0,
            'source_type' => 'custom_url',
        ]);
    }

    protected function tearDown(): void
    {
        $_ENV['INSTALLED'] = 'false';
        $_SERVER['INSTALLED'] = 'false';
        parent::tearDown();
    }

    public function test_create_menu_item_view_does_not_500(): void
    {
        $response = $this->actingAs($this->admin, 'member')
            ->get("/admin/menus/{$this->menu->id}/items/create");

        $this->assertLessThan(500, $response->status(), 'create view 500ed: '.$response->status());
    }

    public function test_edit_menu_item_view_does_not_500(): void
    {
        $response = $this->actingAs($this->admin, 'member')
            ->get("/admin/menus/items/edit/{$this->menuItem->id}");

        $this->assertLessThan(500, $response->status(), 'edit view 500ed: '.$response->status());
    }
}
