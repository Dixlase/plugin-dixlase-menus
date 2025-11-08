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

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dixlase Menu Admin Routes
|--------------------------------------------------------------------------
|
| 管理画面用のルート定義
| 
| 注意: このファイルは自動的に以下のミドルウェアが適用されます
| - web: セッション、CSRF保護
| - auth:member: 管理者認証
| - admin.ip: 管理画面IPアドレス制限
|
*/

// メニュー管理用のルート（今後実装）
// Route::resource('menus', MenuController::class);
// Route::get('menus/{menu}/items', [MenuItemController::class, 'index'])->name('menus.items.index');
