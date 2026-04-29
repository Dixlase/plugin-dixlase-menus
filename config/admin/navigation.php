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

return [
    // メニュー管理
    'menus' => [
        '_insert_before' => 'media',
        'text' => 'dixlase-menus::admin/navigation.menus.text',
        'icon' => 'fas fa-fw fa-bars',
        'can' => 'admin',
        'children' => [
            'index' => [
                'text' => 'dixlase-menus::admin/navigation.menus.index',
                'route' => 'dixlase-menus::admin.menus.index',
                'icon' => 'fas fa-fw fa-list',
                'can' => 'admin',
            ],
            'create' => [
                'text' => 'dixlase-menus::admin/navigation.menus.create',
                'route' => 'dixlase-menus::admin.menus.create',
                'icon' => 'fas fa-fw fa-plus',
                'can' => 'admin',
            ],
        ],
    ],
];
