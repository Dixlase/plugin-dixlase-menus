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
            'max_menu_depth' => ['required', 'integer', 'min:1', 'max:10'],
            'enable_menu_cache' => ['nullable', 'boolean'],
            'cache_duration' => ['required', 'integer', 'min:60', 'max:86400'],
            'default_target' => ['required', 'string', 'in:_self,_blank,_parent,_top'],
            'available_locations' => ['nullable', 'array'],
            'available_locations.*.key' => ['required', 'string', 'max:255'],
            'available_locations.*.label' => ['required', 'string', 'max:255'],
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
            'max_menu_depth.required' => __('dixlase-menu::validation.max_menu_depth_required'),
            'max_menu_depth.integer' => __('dixlase-menu::validation.max_menu_depth_integer'),
            'max_menu_depth.min' => __('dixlase-menu::validation.max_menu_depth_min'),
            'max_menu_depth.max' => __('dixlase-menu::validation.max_menu_depth_max'),
            'cache_duration.required' => __('dixlase-menu::validation.cache_duration_required'),
            'cache_duration.integer' => __('dixlase-menu::validation.cache_duration_integer'),
            'cache_duration.min' => __('dixlase-menu::validation.cache_duration_min'),
            'cache_duration.max' => __('dixlase-menu::validation.cache_duration_max'),
            'default_target.required' => __('dixlase-menu::validation.default_target_required'),
            'default_target.in' => __('dixlase-menu::validation.default_target_invalid'),
            'available_locations.*.key.required' => __('dixlase-menu::validation.location_key_required'),
            'available_locations.*.label.required' => __('dixlase-menu::validation.location_label_required'),
        ];
    }

    /**
     * バリデーション前の処理
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // enable_menu_cacheがない場合はfalseとして扱う
        if (!$this->has('enable_menu_cache')) {
            $this->merge(['enable_menu_cache' => false]);
        }
    }
}
