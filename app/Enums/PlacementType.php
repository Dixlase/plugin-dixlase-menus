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

namespace Plugins\DixlaseMenu\App\Enums;

/**
 * メニュー配置タイプ
 */
enum PlacementType: string
{
    case Manual = 'manual';
    case Auto = 'auto';

    /**
     * 翻訳済みラベルを取得
     */
    public function label(): string
    {
        return __('dixlase-menu::admin.enums.placement_type.' . $this->value);
    }

    /**
     * 全オプションを配列で取得（value => label）
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * ラジオカードグループ用のオプション配列を取得
     *
     * @return array<int, array{value: string, label: string, description: string, icon: string}>
     */
    public static function getRadioCardOptions(): array
    {
        return [
            [
                'value' => self::Manual->value,
                'label' => self::Manual->label(),
                'description' => __('dixlase-menu::admin.enums.placement_type.manual_description'),
                'icon' => 'fas fa-code',
            ],
            [
                'value' => self::Auto->value,
                'label' => self::Auto->label(),
                'description' => __('dixlase-menu::admin.enums.placement_type.auto_description'),
                'icon' => 'fas fa-magic',
            ],
        ];
    }
}
