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

namespace Plugins\DixlaseMenus\App\Shortcodes;

use Plugins\DixlaseMenus\App\Helpers\MenuHelper;

/**
 * メニューショートコード
 * 
 * 使用例:
 *   [menu]                          - デフォルトテンプレートでメニューを表示
 *   [menu template="horizontal"]    - 水平メニューテンプレートを使用
 *   [menu class="my-menu"]          - カスタムCSSクラスを追加
 *   [menu id="main-nav"]            - カスタムIDを設定
 *   [menu show_children="false"]    - 子メニューを非表示
 */
class MenuShortcode
{
    /**
     * ショートコードをレンダリング
     *
     * @param array $attributes ショートコードの属性
     * @param string|null $content ショートコードの内容
     * @return string
     */
    public function render($attributes = [], $content = null): string
    {
        // 属性を正規化
        $options = $this->normalizeAttributes($attributes);
        
        try {
            return MenuHelper::render($options);
        } catch (\Exception $e) {
            \Log::error('MenuShortcode render error: ' . $e->getMessage());
            return '<!-- Menu error: ' . e($e->getMessage()) . ' -->';
        }
    }

    /**
     * 属性を正規化
     *
     * @param array $attributes
     * @return array
     */
    protected function normalizeAttributes(array $attributes): array
    {
        $options = [];

        // スラッグ
        if (isset($attributes['slug'])) {
            $options['slug'] = $attributes['slug'];
        }

        // ロケーション
        if (isset($attributes['location'])) {
            $options['location'] = $attributes['location'];
        }

        // テンプレート
        if (isset($attributes['template'])) {
            $options['template'] = $attributes['template'];
        }
        
        // CSSクラス
        if (isset($attributes['class'])) {
            $options['class'] = $attributes['class'];
        }
        
        // ID
        if (isset($attributes['id'])) {
            $options['id'] = $attributes['id'];
        }
        
        // 階層の深さ
        if (isset($attributes['depth'])) {
            $options['depth'] = (int) $attributes['depth'];
        }
        
        // 子メニュー表示
        if (isset($attributes['show_children'])) {
            $options['show_children'] = filter_var($attributes['show_children'], FILTER_VALIDATE_BOOLEAN);
        }
        
        return $options;
    }
}
