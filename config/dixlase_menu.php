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
    /*
    |--------------------------------------------------------------------------
    | Dixlase Menu Configuration
    |--------------------------------------------------------------------------
    |
    | メニュー管理プラグインの設定
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Menu Location
    |--------------------------------------------------------------------------
    |
    | デフォルトのメニュー表示位置
    |
    */

    'default_location' => 'header',

    /*
    |--------------------------------------------------------------------------
    | Max Menu Depth
    |--------------------------------------------------------------------------
    |
    | メニューの最大階層数
    |
    */

    'max_depth' => 5,

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | メニューのキャッシュ設定
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Assets
    |--------------------------------------------------------------------------
    |
    | プラグインのアセット設定
    | common: 全ページで読み込むアセット
    | admin: 管理画面でのみ読み込むアセット
    |
    */

    'assets' => [
        'common' => ['js/app.js'],
        'admin' => ['admin/js/app.js'],
        'front' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | メニューのキャッシュ設定
    |
    */

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1時間
        'key_prefix' => 'dixlase_menu_',
    ],
];
