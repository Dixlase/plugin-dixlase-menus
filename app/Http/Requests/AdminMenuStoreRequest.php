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
 * メニュー作成リクエスト
 */
class AdminMenuStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-_]+$/'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
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
            'name.required' => __('dixlase-menu::validation.name_required'),
            'name.max' => __('dixlase-menu::validation.name_max'),
            'slug.required' => __('dixlase-menu::validation.slug_required'),
            'slug.max' => __('dixlase-menu::validation.slug_max'),
            'slug.regex' => __('dixlase-menu::validation.slug_format'),
            'location.max' => __('dixlase-menu::validation.location_max'),
            'description.max' => __('dixlase-menu::validation.description_max'),
            'display_order.integer' => __('dixlase-menu::validation.display_order_integer'),
            'display_order.min' => __('dixlase-menu::validation.display_order_min'),
        ];
    }

    /**
     * バリデーション前の処理
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // is_activeがない場合はfalseとして扱う
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => false]);
        }

        // display_orderがない場合は0として扱う
        if (!$this->has('display_order')) {
            $this->merge(['display_order' => 0]);
        }
    }
}
