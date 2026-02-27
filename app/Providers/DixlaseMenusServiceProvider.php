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

namespace Plugins\DixlaseMenus\App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Traits\PluginLoaderTrait;
use App\Helpers\PluginHelper;
use Plugins\DixlaseMenus\App\Services\MenuLinkSourceManager;
use Plugins\DixlaseMenus\App\Services\MenuLinkSources\CustomUrlSource;
use Plugins\DixlaseMenus\App\Services\MenuLinkSources\LinkableProviderAdapter;
use App\Contracts\PluginIntegration\LinkableProviderInterface;
use Plugins\DixlaseMenus\App\Shortcodes\MenuShortcode;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuItemRepositoryInterface;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuSettingRepositoryInterface;
use Plugins\DixlaseMenus\App\Repositories\MenuRepository;
use Plugins\DixlaseMenus\App\Repositories\MenuItemRepository;
use Plugins\DixlaseMenus\App\Repositories\MenuSettingRepository;
use Plugins\DixlaseMenus\App\Services\MenuService;
use Plugins\DixlaseMenus\App\Models\Menu;
use Plugins\DixlaseMenus\App\Models\MenuItem;
use Plugins\DixlaseMenus\App\Observers\MenuObserver;
use Plugins\DixlaseMenus\App\Observers\MenuItemObserver;

class DixlaseMenusServiceProvider extends ServiceProvider
{
    use PluginLoaderTrait;
    /**
     * Register services.
     */
    public function register(): void
    {
        // 設定ファイルをマージ
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/dixlase_menus.php',
            'dixlase_menus'
        );

        // MenuLinkSourceManagerをシングルトンとして登録
        $this->app->singleton(MenuLinkSourceManager::class, function ($app) {
            return new MenuLinkSourceManager();
        });

        // リポジトリをバインド
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(MenuSettingRepositoryInterface::class, MenuSettingRepository::class);

        // MenuServiceをシングルトンとして登録
        $this->app->singleton(MenuService::class, function ($app) {
            return new MenuService(
                $app->make(MenuRepositoryInterface::class),
                $app->make(MenuItemRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // ビューの登録
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'dixlase-menus');

        // 翻訳ファイルの登録
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'dixlase-menus');

        // マイグレーションの登録
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // モデルオブザーバーの登録（キャッシュ自動破棄）
        Menu::observe(MenuObserver::class);
        MenuItem::observe(MenuItemObserver::class);

        // リンクソースの登録
        $this->registerLinkSources();

        // ショートコードの登録
        $this->registerShortcodes();

        // Bladeディレクティブの登録
        $this->registerBladeDirectives();

        // 注: ルート（routes/web.php, routes/admin.php）はPluginServiceProviderが自動読み込み

        // 公開可能なアセット
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/dixlase_menus.php' => config_path('dixlase_menus.php'),
            ], 'dixlase-menus-config');

            $this->publishes([
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/dixlase-menus'),
            ], 'dixlase-menus-views');
        }
    }

    /**
     * リンクソースを登録
     *
     * カスタムURLソースを登録後、コアの linkable.providers タグで
     * 登録されたプロバイダーを自動検出し、アダプター経由で登録する
     */
    protected function registerLinkSources(): void
    {
        $manager = $this->app->make(MenuLinkSourceManager::class);
        $manager->register(new CustomUrlSource());

        // コアの linkable.providers タグ付きプロバイダーを自動検出
        try {
            $providers = $this->app->tagged('linkable.providers');
            foreach ($providers as $provider) {
                if ($provider instanceof LinkableProviderInterface) {
                    $manager->register(new LinkableProviderAdapter($provider));
                }
            }
        } catch (\Exception $e) {
            // タグ未登録の場合は無視
        }
    }

    /**
     * ショートコードを登録
     */
    protected function registerShortcodes(): void
    {
        // コアのPluginHelperを使用してショートコードを登録
        PluginHelper::registerShortcode('menu', MenuShortcode::class);
    }

    /**
     * Bladeディレクティブを登録
     */
    protected function registerBladeDirectives(): void
    {
        // @menu('slug') / @menu('slug', ['template' => 'horizontal'])
        Blade::directive('menu', function ($expression) {
            return "<?php echo \Plugins\DixlaseMenus\App\Helpers\MenuHelper::renderDirective({$expression}); ?>";
        });
    }
}
