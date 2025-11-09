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

namespace Plugins\DixlaseMenu\App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Traits\PluginLoaderTrait;
use Plugins\DixlaseMenu\App\Services\MenuLinkSourceManager;
use Plugins\DixlaseMenu\App\Services\MenuLinkSources\CustomUrlSource;
use Plugins\DixlaseMenu\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenu\App\Contracts\Repositories\MenuItemRepositoryInterface;
use Plugins\DixlaseMenu\App\Contracts\Repositories\MenuSettingRepositoryInterface;
use Plugins\DixlaseMenu\App\Repositories\MenuRepository;
use Plugins\DixlaseMenu\App\Repositories\MenuItemRepository;
use Plugins\DixlaseMenu\App\Repositories\MenuSettingRepository;

class DixlaseMenuServiceProvider extends ServiceProvider
{
    use PluginLoaderTrait;
    /**
     * Register services.
     */
    public function register(): void
    {
        // 設定ファイルをマージ
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/dixlase_menu.php',
            'dixlase_menu'
        );

        // 管理画面ナビゲーションをマージ
        $this->mergeAdminNavigation('DixlaseMenu', __DIR__ . '/../../config/admin.php');

        // MenuLinkSourceManagerをシングルトンとして登録
        $this->app->singleton(MenuLinkSourceManager::class, function ($app) {
            return new MenuLinkSourceManager();
        });

        // リポジトリをバインド
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(MenuSettingRepositoryInterface::class, MenuSettingRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // ビューの登録
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'dixlase-menu');

        // 翻訳ファイルの登録
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'dixlase-menu');

        // マイグレーションの登録
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // リンクソースの登録
        $this->registerLinkSources();

        // ルートの登録
        $this->registerRoutes();

        // 公開可能なアセット
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/dixlase_menu.php' => config_path('dixlase_menu.php'),
            ], 'dixlase-menu-config');

            $this->publishes([
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/dixlase-menu'),
            ], 'dixlase-menu-views');
        }
    }

    /**
     * リンクソースを登録
     */
    protected function registerLinkSources(): void
    {
        $manager = $this->app->make(MenuLinkSourceManager::class);

        // カスタムURLソースを登録
        $manager->register(new CustomUrlSource());

        // 他のプラグインがリンクソースを追加できるようにイベントを発火
        // event(new MenuLinkSourcesRegistering($manager));
    }

    /**
     * ルートを登録
     * 
     * 注意: plugin.web と plugin.admin ミドルウェアグループは
     * コアによって強制的にセキュリティミドルウェアが適用されます。
     * これらのミドルウェアグループを変更しないでください。
     */
    protected function registerRoutes(): void
    {
        // Web routes (フロントエンド)
        // plugin.web グループにより以下が自動適用されます:
        // - セッション管理
        // - CSRF保護
        // - IP制限（front.ip）← コアにより強制
        if (file_exists(__DIR__ . '/../../routes/web.php')) {
            Route::middleware(['plugin.web'])
                ->group(__DIR__ . '/../../routes/web.php');
        }

        // Admin routes (管理画面)
        // plugin.admin グループにより以下が自動適用されます:
        // - セッション管理
        // - CSRF保護
        // - 認証（auth:member）← コアにより強制
        // - IP制限（admin.ip）← コアにより強制
        if (file_exists(__DIR__ . '/../../routes/admin.php')) {
            Route::middleware(['plugin.admin'])
                ->prefix(config('admin.url', 'admin'))
                ->name('admin.')
                ->group(__DIR__ . '/../../routes/admin.php');
        }
    }
}
