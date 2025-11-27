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

namespace Plugins\DixlaseMenu\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * メニュー設定更新リクエスト
 */
class AdminMenuSettingsUpdateRequest extends FormRequest
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
            'default_target' => ['nullable', 'string', 'in:_self,_blank,_parent,_top'],
            'menu_items' => ['nullable', 'array'],
            'menu_items.*.label' => ['required', 'string', 'max:255'],
            'menu_items.*.url' => ['nullable', 'string', 'max:2048'],
            'menu_items.*.target' => ['nullable', 'string', 'in:_self,_blank,_parent,_top'],
            'menu_items.*.source_type' => ['nullable', 'string', 'max:50'],
            'menu_items.*.source_id' => ['nullable', 'string', 'max:255'],
            'menu_items.*.source_provider' => ['nullable', 'string', 'max:255'],
            'menu_items.*.children' => ['nullable', 'array', 'max:3'],
            'menu_items.*.children.*.label' => ['required', 'string', 'max:255'],
            'menu_items.*.children.*.url' => ['nullable', 'string', 'max:2048'],
            'menu_items.*.children.*.target' => ['nullable', 'string', 'in:_self,_blank,_parent,_top'],
            'menu_items.*.children.*.source_type' => ['nullable', 'string', 'max:50'],
            'menu_items.*.children.*.source_id' => ['nullable', 'string', 'max:255'],
            'menu_items.*.children.*.source_provider' => ['nullable', 'string', 'max:255'],
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
            'default_target.in' => __('dixlase-menu::validation.default_target_invalid'),
            'menu_items.*.label.required' => __('dixlase-menu::validation.menu_label_required'),
            'menu_items.*.label.max' => __('dixlase-menu::validation.menu_label_max'),
            'menu_items.*.url.max' => __('dixlase-menu::validation.menu_url_max'),
            'menu_items.*.target.in' => __('dixlase-menu::validation.menu_target_invalid'),
            'menu_items.*.children.max' => __('dixlase-menu::validation.menu_children_max'),
            'menu_items.*.children.*.label.required' => __('dixlase-menu::validation.menu_label_required'),
            'menu_items.*.children.*.label.max' => __('dixlase-menu::validation.menu_label_max'),
            'menu_items.*.children.*.url.max' => __('dixlase-menu::validation.menu_url_max'),
            'menu_items.*.children.*.target.in' => __('dixlase-menu::validation.menu_target_invalid'),
        ];
    }
}
