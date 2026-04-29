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

namespace Plugins\DixlaseMenus\App\Helpers;

use Plugins\DixlaseMenus\App\Services\MenuService;
use Plugins\DixlaseMenus\App\Repositories\MenuSettingRepository;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class MenuHelper
{
    /**
     * メニューアイテムを取得（デフォルトメニュー）
     *
     * @return array
     */
    public static function getMenuItems(): array
    {
        try {
            $menuService = app(MenuService::class);
            $menu = $menuService->getDefaultMenu();
            return $menu ? ($menu['items'] ?? []) : [];
        } catch (\Exception $e) {
            Log::error('MenuHelper: Failed to get menu items', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * スラッグでメニューアイテムを取得
     *
     * @param string $slug
     * @return array
     */
    public static function getMenuItemsBySlug(string $slug): array
    {
        try {
            $menuService = app(MenuService::class);
            $menu = $menuService->getMenuBySlug($slug);
            return $menu ? ($menu['items'] ?? []) : [];
        } catch (\Exception $e) {
            Log::error('MenuHelper: Failed to get menu items by slug', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * ロケーションでメニューアイテムを取得
     *
     * @param string $location
     * @return array
     */
    public static function getMenuItemsByLocation(string $location): array
    {
        try {
            $menuService = app(MenuService::class);
            $menu = $menuService->getMenuByLocation($location);
            return $menu ? ($menu['items'] ?? []) : [];
        } catch (\Exception $e) {
            Log::error('MenuHelper: Failed to get menu items by location', [
                'location' => $location,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * デフォルトターゲットを取得
     *
     * @return string
     */
    public static function getDefaultTarget(): string
    {
        try {
            $repository = app(MenuSettingRepository::class);
            return $repository->get('default_target', '_self');
        } catch (\Exception $e) {
            return '_self';
        }
    }

    /**
     * Bladeディレクティブからメニューをレンダリング
     *
     * @param string $slug メニューのスラッグ
     * @param array $options 追加オプション
     * @return string
     */
    public static function renderDirective(string $slug, array $options = []): string
    {
        $options['slug'] = $slug;

        return static::render($options);
    }

    /**
     * メニューをHTMLとしてレンダリング
     *
     * @param array $options オプション
     *   - slug: メニューのスラッグ（指定しない場合はデフォルトメニュー）
     *   - location: メニューのロケーション
     *   - template: 使用するテンプレート名 (default: 'default')
     *   - class: メニューのCSSクラス
     *   - id: メニューのID
     *   - depth: 表示する階層の深さ (0 = 無制限)
     *   - show_children: 子メニューを表示するか (default: true)
     * @return string
     */
    public static function render(array $options = []): string
    {
        // メニューアイテムを取得
        if (!empty($options['slug'])) {
            $items = self::getMenuItemsBySlug($options['slug']);
        } elseif (!empty($options['location'])) {
            $items = self::getMenuItemsByLocation($options['location']);
        } else {
            $items = self::getMenuItems();
        }
        
        if (empty($items)) {
            return '';
        }

        $template = $options['template'] ?? 'default';
        $viewName = "dixlase-menus::front.menus.{$template}";
        
        // テンプレートが存在しない場合はデフォルトを使用
        if (!View::exists($viewName)) {
            $viewName = 'dixlase-menus::front.menus.default';
        }

        try {
            return view($viewName, [
                'items' => $items,
                'options' => $options,
                'defaultTarget' => self::getDefaultTarget(),
            ])->render();
        } catch (\Exception $e) {
            Log::error('MenuHelper: Failed to render menu', [
                'error' => $e->getMessage(),
                'template' => $template,
            ]);
            return '';
        }
    }

    /**
     * メニューアイテムをリスト形式で取得（カスタム表示用）
     *
     * @param bool $includeChildren 子メニューを含めるか
     * @param string|null $slug メニューのスラッグ
     * @return array
     */
    public static function getItems(bool $includeChildren = true, ?string $slug = null): array
    {
        $items = $slug ? self::getMenuItemsBySlug($slug) : self::getMenuItems();
        
        if (!$includeChildren) {
            return array_map(function ($item) {
                unset($item['children']);
                return $item;
            }, $items);
        }
        
        return $items;
    }

    /**
     * メニューが存在するか確認
     *
     * @param string|null $slug メニューのスラッグ
     * @return bool
     */
    public static function hasMenu(?string $slug = null): bool
    {
        $items = $slug ? self::getMenuItemsBySlug($slug) : self::getMenuItems();
        return !empty($items);
    }
}
