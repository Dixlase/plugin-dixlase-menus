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

namespace Plugins\DixlaseMenu\App\Helpers;

use Plugins\DixlaseMenu\App\Repositories\MenuSettingRepository;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class MenuHelper
{
    /**
     * メニューアイテムを取得
     *
     * @return array
     */
    public static function getMenuItems(): array
    {
        try {
            $repository = app(MenuSettingRepository::class);
            return $repository->getJson('menu_items', []);
        } catch (\Exception $e) {
            Log::error('MenuHelper: Failed to get menu items', [
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
     * メニューをHTMLとしてレンダリング
     *
     * @param array $options オプション
     *   - template: 使用するテンプレート名 (default: 'default')
     *   - class: メニューのCSSクラス
     *   - id: メニューのID
     *   - depth: 表示する階層の深さ (0 = 無制限)
     *   - show_children: 子メニューを表示するか (default: true)
     * @return string
     */
    public static function render(array $options = []): string
    {
        $items = self::getMenuItems();
        
        if (empty($items)) {
            return '';
        }

        $template = $options['template'] ?? 'default';
        $viewName = "dixlase-menu::front.menus.{$template}";
        
        // テンプレートが存在しない場合はデフォルトを使用
        if (!View::exists($viewName)) {
            $viewName = 'dixlase-menu::front.menus.default';
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
     * @return array
     */
    public static function getItems(bool $includeChildren = true): array
    {
        $items = self::getMenuItems();
        
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
     * @return bool
     */
    public static function hasMenu(): bool
    {
        return !empty(self::getMenuItems());
    }
}
