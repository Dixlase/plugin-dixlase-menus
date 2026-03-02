<?php

/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
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

namespace Plugins\DixlaseMenus\Tests\Feature\Admin;

use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuSettingRepositoryInterface;
use Plugins\DixlaseMenus\App\Http\Controllers\Admin\AdminMenuController;
use Plugins\DixlaseMenus\App\Repositories\MenuRepository;
use Plugins\DixlaseMenus\App\Repositories\MenuSettingRepository;
use Tests\TestCase;

/**
 * メニュースラッグ一意性バリデーションのフィーチャーテスト
 */
class MenuSlugUniquenessTest extends TestCase
{
    use RefreshDatabase;

    private Member $admin;

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

        // リポジトリバインディングを登録
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(MenuSettingRepositoryInterface::class, MenuSettingRepository::class);

        // ルートを手動登録
        $adminUrl = config('admin.admin_url', 'admin');
        $router = app('router');
        $router->prefix($adminUrl)
            ->middleware(['web', 'auth:member'])
            ->group(function () use ($router) {
                $router->prefix('menus')
                    ->name('dixlase-menus::admin.menus.')
                    ->group(function () use ($router) {
                        $router->post('/', [AdminMenuController::class, 'store'])->name('store');
                        $router->get('/{id}/edit', [AdminMenuController::class, 'edit'])->name('edit');
                        $router->put('/{id}', [AdminMenuController::class, 'update'])->name('update');
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
     * 新規作成時に既存スラッグと重複するとバリデーションエラーになること
     */
    public function test_store_fails_when_slug_already_exists(): void
    {
        DB::table('plg_dixlase_menus')->insert([
            'name' => 'Existing Menu',
            'slug' => 'main-menu',
            'lang' => 'en',
            'is_active' => true,
            'display_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'member')
            ->from(route('dixlase-menus::admin.menus.store'))
            ->post(route('dixlase-menus::admin.menus.store'), [
                'name' => 'New Menu',
                'slug' => 'main-menu',
                'placement_type' => 'manual',
            ]);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * 更新時に自分自身のスラッグは許可されること
     */
    public function test_update_allows_own_slug(): void
    {
        $menuId = DB::table('plg_dixlase_menus')->insertGetId([
            'name' => 'My Menu',
            'slug' => 'my-menu',
            'lang' => 'en',
            'is_active' => true,
            'display_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'member')
            ->from(route('dixlase-menus::admin.menus.update', ['id' => $menuId]))
            ->put(route('dixlase-menus::admin.menus.update', ['id' => $menuId]), [
                'name' => 'My Menu Updated',
                'slug' => 'my-menu',
                'placement_type' => 'manual',
            ]);

        $response->assertSessionDoesntHaveErrors('slug');
    }

    /**
     * 更新時に他メニューのスラッグと重複するとバリデーションエラーになること
     */
    public function test_update_fails_when_slug_belongs_to_another_menu(): void
    {
        DB::table('plg_dixlase_menus')->insert([
            'name' => 'Other Menu',
            'slug' => 'other-menu',
            'lang' => 'en',
            'is_active' => true,
            'display_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $menuId = DB::table('plg_dixlase_menus')->insertGetId([
            'name' => 'My Menu',
            'slug' => 'my-menu',
            'lang' => 'en',
            'is_active' => true,
            'display_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'member')
            ->from(route('dixlase-menus::admin.menus.update', ['id' => $menuId]))
            ->put(route('dixlase-menus::admin.menus.update', ['id' => $menuId]), [
                'name' => 'My Menu',
                'slug' => 'other-menu',
                'placement_type' => 'manual',
            ]);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * ソフトデリート済みメニューのスラッグは再利用可能であること
     */
    public function test_soft_deleted_menu_slug_can_be_reused(): void
    {
        DB::table('plg_dixlase_menus')->insert([
            'name' => 'Deleted Menu',
            'slug' => 'reusable-slug',
            'lang' => 'en',
            'is_active' => true,
            'display_order' => 0,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'member')
            ->from(route('dixlase-menus::admin.menus.store'))
            ->post(route('dixlase-menus::admin.menus.store'), [
                'name' => 'New Menu',
                'slug' => 'reusable-slug',
                'placement_type' => 'manual',
            ]);

        $response->assertSessionDoesntHaveErrors('slug');
    }
}
