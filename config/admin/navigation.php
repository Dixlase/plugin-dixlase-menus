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
    // メニュー管理
    'menus' => [
        '_insert_after' => 'media',
        'text' => 'dixlase-menu::admin/navigation.menus.text',
        'icon' => 'fas fa-fw fa-bars',
        'can' => 'admin',
        'children' => [
            'index' => [
                'text' => 'dixlase-menu::admin/navigation.menus.index',
                'route' => 'dixlase-menu::admin.menus.index',
                'icon' => 'fas fa-fw fa-list',
                'can' => 'admin',
            ],
            'settings' => [
                'text' => 'dixlase-menu::admin/navigation.menus.settings',
                'route' => 'dixlase-menu::admin.settings.menus',
                'icon' => 'fas fa-fw fa-cog',
                'can' => 'admin',
            ],
        ],
    ],
];
