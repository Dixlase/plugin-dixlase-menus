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

namespace Plugins\DixlaseMenus\Tests\Feature\Http\Controllers\Admin;

use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Plugins\DixlaseMenus\App\Contracts\MenuLinkSource;
use Plugins\DixlaseMenus\App\Http\Controllers\Admin\AdminMenuLinkSourceController;
use Plugins\DixlaseMenus\App\Services\MenuLinkSourceManager;
use Plugins\DixlaseMenus\App\Services\MenuLinkSources\CustomUrlSource;
use Tests\TestCase;

/**
 * AdminMenuLinkSourceController フィーチャーテスト
 */
class AdminMenuLinkSourceControllerTest extends TestCase
{
    use RefreshDatabase;

    private Member $admin;

    private MenuLinkSourceManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['INSTALLED'] = 'true';
        $_SERVER['INSTALLED'] = 'true';

        $this->artisan('migrate', [
            '--path' => base_path('plugins/DixlaseMenus/database/migrations'),
            '--realpath' => true,
        ]);

        $this->app['translator']->addNamespace(
            'dixlase-menus',
            base_path('plugins/DixlaseMenus/lang')
        );

        // MenuLinkSourceManager をシングルトン登録し CustomUrlSource を追加
        $this->manager = new MenuLinkSourceManager();
        $this->manager->register(new CustomUrlSource());
        $this->app->instance(MenuLinkSourceManager::class, $this->manager);

        // ルートを手動登録
        $adminUrl = config('admin.admin_url', 'admin');
        $router = app('router');
        $router->prefix($adminUrl)
            ->middleware(['web', 'auth:member'])
            ->group(function () use ($router) {
                $router->prefix('menus/link-sources')
                    ->name('dixlase-menus::admin.menus.link-sources.')
                    ->group(function () use ($router) {
                        $router->get('/', [AdminMenuLinkSourceController::class, 'index'])->name('index');
                        $router->get('/{sourceType}/items', [AdminMenuLinkSourceController::class, 'items'])->name('items');
                    });
            });

        $router->getRoutes()->refreshNameLookups();
        $router->getRoutes()->refreshActionLookups();

        $this->admin = Member::create([
            'account_name' => 'testadmin',
            'display_name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => MemberRole::SUPER_ADMIN,
            'status' => MemberStatus::Active,
        ]);
    }

    protected function tearDown(): void
    {
        $_ENV['INSTALLED'] = 'false';
        $_SERVER['INSTALLED'] = 'false';
        parent::tearDown();
    }

    /**
     * ソース一覧エンドポイントが正しい JSON 構造を返すこと
     */
    public function test_index_returns_sources_metadata(): void
    {
        $response = $this->actingAs($this->admin, 'member')
            ->getJson(route('dixlase-menus::admin.menus.link-sources.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'sources' => [
                '*' => ['type', 'label', 'icon', 'hasItems'],
            ],
        ]);
    }

    /**
     * ソース一覧に custom_url が含まれること
     */
    public function test_index_includes_custom_url_source(): void
    {
        $response = $this->actingAs($this->admin, 'member')
            ->getJson(route('dixlase-menus::admin.menus.link-sources.index'));

        $response->assertOk();

        $sources = $response->json('sources');
        $customUrl = collect($sources)->firstWhere('type', 'custom_url');

        $this->assertNotNull($customUrl);
        $this->assertFalse($customUrl['hasItems']);
    }

    /**
     * 不明なソースタイプで 404 を返すこと
     */
    public function test_items_returns_404_for_unknown_source(): void
    {
        $response = $this->actingAs($this->admin, 'member')
            ->getJson(route('dixlase-menus::admin.menus.link-sources.items', [
                'sourceType' => 'nonexistent',
            ]));

        $response->assertNotFound();
        $response->assertJson(['error' => 'Source not found']);
    }

    /**
     * 登録済みソースのアイテムが返されること
     */
    public function test_items_returns_items_for_registered_source(): void
    {
        $mockSource = $this->createMock(MenuLinkSource::class);
        $mockSource->method('getSourceType')->willReturn('test-source');
        $mockSource->method('getSourceLabel')->willReturn('Test Source');
        $mockSource->method('isAvailable')->willReturn(true);
        $mockSource->method('getAvailableItems')->willReturn([
            ['id' => '1', 'title' => 'Item 1', 'url' => '/item-1'],
            ['id' => '2', 'title' => 'Item 2', 'url' => '/item-2'],
        ]);

        $this->manager->register($mockSource);

        $response = $this->actingAs($this->admin, 'member')
            ->getJson(route('dixlase-menus::admin.menus.link-sources.items', [
                'sourceType' => 'test-source',
            ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'items');
        $response->assertJsonPath('items.0.title', 'Item 1');
        $response->assertJsonPath('items.1.title', 'Item 2');
    }

    /**
     * 検索パラメータがソースに渡されること
     */
    public function test_items_passes_search_query_to_source(): void
    {
        $mockSource = $this->createMock(MenuLinkSource::class);
        $mockSource->method('getSourceType')->willReturn('test-source');
        $mockSource->method('getSourceLabel')->willReturn('Test Source');
        $mockSource->method('isAvailable')->willReturn(true);
        $mockSource->method('searchItems')
            ->with('hello', 20)
            ->willReturn([
                ['id' => '1', 'title' => 'Hello Page', 'url' => '/hello'],
            ]);

        $this->manager->register($mockSource);

        $response = $this->actingAs($this->admin, 'member')
            ->getJson(route('dixlase-menus::admin.menus.link-sources.items', [
                'sourceType' => 'test-source',
                'search' => 'hello',
            ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'items');
        $response->assertJsonPath('items.0.title', 'Hello Page');
    }
}
