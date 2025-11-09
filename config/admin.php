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

return [
    // プラグイン設定画面のルート名
    'settings_route' => 'dixlase-menu::admin.settings.menus.index',

    /*
    |--------------------------------------------------------------------------
    | Admin Navigation
    |--------------------------------------------------------------------------
    */
    'nav' => [
        'settings' => [
            'text' => 'admin.nav.settings.text', // 全体設定
            'icon' => 'fas fa-fw fa-cogs',
            'can' => 'admin',
            'children' => [
                'menus' => [
                    'text' => 'dixlase-menu::admin.nav.settings.menus',
                    'route' => 'dixlase-menu::admin.settings.menus.index',
                    'icon' => 'fas fa-fw fa-bars',
                    'can' => 'admin',
                ],
            ],
        ],
    ],
];
