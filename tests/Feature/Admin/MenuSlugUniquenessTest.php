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

        // Seed primary site (required by SiteContext after core update)
        $this->seed(\Database\Seeders\SitesSeeder::class);

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

        // === TEMP DIAGNOSTIC — remove before merging ===
        $errs = session()->get('errors');
        $errsStr = is_null($errs) ? 'NULL' : (is_object($errs) ? get_class($errs).':'.json_encode($errs->getBag('default')->all()) : var_export($errs, true));
        fwrite(STDERR, PHP_EOL.'[DIAG] status='.$response->status().PHP_EOL);
        fwrite(STDERR, '[DIAG] location='.($response->headers->get('Location') ?? 'NULL').PHP_EOL);
        fwrite(STDERR, '[DIAG] session_errors='.$errsStr.PHP_EOL);
        fwrite(STDERR, '[DIAG] session_all='.json_encode(session()->all()).PHP_EOL);
        fwrite(STDERR, '[DIAG] rows='.json_encode(\DB::table('plg_dixlase_menus')->get()->toArray()).PHP_EOL);
        fwrite(STDERR, '[DIAG] route_store='.route('dixlase-menus::admin.menus.store').PHP_EOL);
        fwrite(STDERR, '[DIAG] admin_url='.var_export(config('admin.admin_url'), true).PHP_EOL);
        fwrite(STDERR, '[DIAG] db_conn='.config('database.default').PHP_EOL);
        fwrite(STDERR, '[DIAG] session_drv='.config('session.driver').PHP_EOL);
        fwrite(STDERR, '[DIAG] installed_env='.var_export(env('INSTALLED'), true).PHP_EOL);
        fwrite(STDERR, '[DIAG] exception='.($response->exception ? get_class($response->exception).':'.$response->exception->getMessage() : 'none').PHP_EOL);
        fwrite(STDERR, '[DIAG] body_head='.substr($response->getContent(), 0, 400).PHP_EOL);

        // Enumerate all registered routes matching POST /admin/menus/
        $router = app('router');
        $routes = $router->getRoutes();
        $matches = [];
        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            if (in_array('POST', $methods, true) && str_contains($uri, 'menus') && !str_contains($uri, '{')) {
                $matches[] = [
                    'uri' => $uri,
                    'name' => $route->getName(),
                    'action' => is_string($route->getActionName()) ? $route->getActionName() : 'closure',
                    'middleware' => $route->gatherMiddleware(),
                ];
            }
        }
        fwrite(STDERR, '[DIAG] matched_routes='.json_encode($matches, JSON_PRETTY_PRINT).PHP_EOL);

        // Retry the same POST with ALL middleware disabled — if this reaches
        // validation and returns errors, the culprit is one of the middleware.
        $bypass = $this->withoutMiddleware()->actingAs($this->admin, 'member')
            ->from(route('dixlase-menus::admin.menus.store'))
            ->post(route('dixlase-menus::admin.menus.store'), [
                'name' => 'New Menu',
                'slug' => 'main-menu',
                'placement_type' => 'manual',
            ]);
        $bErrs = session()->get('errors');
        $bErrsStr = is_null($bErrs) ? 'NULL' : (is_object($bErrs) ? get_class($bErrs).':'.json_encode($bErrs->getBag('default')->all()) : var_export($bErrs, true));
        fwrite(STDERR, '[DIAG-BYPASS] status='.$bypass->status().PHP_EOL);
        fwrite(STDERR, '[DIAG-BYPASS] location='.($bypass->headers->get('Location') ?? 'NULL').PHP_EOL);
        fwrite(STDERR, '[DIAG-BYPASS] session_errors='.$bErrsStr.PHP_EOL);
        fwrite(STDERR, '[DIAG-BYPASS] rows='.json_encode(\DB::table('plg_dixlase_menus')->get()->toArray()).PHP_EOL);
        fwrite(STDERR, '[DIAG-BYPASS] exception='.($bypass->exception ? get_class($bypass->exception).':'.$bypass->exception->getMessage() : 'none').PHP_EOL);
        // === END TEMP DIAGNOSTIC ===

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
