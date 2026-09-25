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

namespace Plugins\DixlaseMenus\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Plugins\DixlaseMenus\App\Support\MenuUrlPolicy;

/**
 * メニューアイテム作成リクエスト
 */
class AdminMenuItemStoreRequest extends FormRequest
{
    /**
     * リクエストの認可
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'title' => ['required', 'string', 'max:255'],
            'source_type' => ['required', 'string', 'max:50'],
            'source_id' => ['nullable', 'string', 'max:255'],
            // Same scheme allowlist as the /sync path (MenuUrlPolicy).
            'url' => ['nullable', 'string', 'max:2048', MenuUrlPolicy::rule()],
            'target' => ['nullable', 'string', 'in:_self,_blank,_parent,_top'],
            'css_class' => ['nullable', 'string', 'max:255'],
            'icon_class' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'visibility_condition' => ['nullable', 'json'],
        ];
    }

    /**
     * バリデーションメッセージ
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => __('dixlase-menus::validation.parent_not_found'),
            'title.required' => __('dixlase-menus::validation.title_required'),
            'title.max' => __('dixlase-menus::validation.title_max'),
            'source_type.required' => __('dixlase-menus::validation.source_type_required'),
            'url.max' => __('dixlase-menus::validation.url_max'),
            'target.in' => __('dixlase-menus::validation.target_invalid'),
            'css_class.max' => __('dixlase-menus::validation.css_class_max'),
            'icon_class.max' => __('dixlase-menus::validation.icon_class_max'),
            'display_order.integer' => __('dixlase-menus::validation.display_order_integer'),
            'display_order.min' => __('dixlase-menus::validation.display_order_min'),
            'visibility_condition.json' => __('dixlase-menus::validation.visibility_condition_json'),
        ];
    }

    /**
     * バリデーション前の処理
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // is_activeがない場合はtrueとして扱う
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // display_orderがない場合は0として扱う
        if (!$this->has('display_order')) {
            $this->merge(['display_order' => 0]);
        }

        // targetがない場合は_selfとして扱う
        if (!$this->has('target')) {
            $this->merge(['target' => '_self']);
        }
    }
}
