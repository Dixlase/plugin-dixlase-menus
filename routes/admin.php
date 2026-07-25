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

use Illuminate\Support\Facades\Route;
use Plugins\DixlaseMenus\App\Http\Controllers\Admin\AdminMenuController;
use Plugins\DixlaseMenus\App\Http\Controllers\Admin\AdminMenuItemController;
use Plugins\DixlaseMenus\App\Http\Controllers\Admin\AdminMenuLinkSourceController;

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

Route::prefix('menus')->name('dixlase-menus::admin.menus.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | メニュー管理
    |--------------------------------------------------------------------------
    */

    // メニュー一覧
    Route::get('/', [AdminMenuController::class, 'index'])->name('index');

    // メニュー作成
    Route::get('/create', [AdminMenuController::class, 'create'])->name('create');
    Route::post('/', [AdminMenuController::class, 'store'])->name('store');

    // メニュー編集
    Route::get('/edit/{id}', [AdminMenuController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminMenuController::class, 'update'])->name('update');

    // メニュー削除
    Route::delete('/{id}', [AdminMenuController::class, 'destroy'])->name('destroy');

    // メニュー復元
    Route::post('/{id}/restore', [AdminMenuController::class, 'restore'])->name('restore');

    /*
    |--------------------------------------------------------------------------
    | メニューアイテム管理（メニューコンテキスト付き）
    |--------------------------------------------------------------------------
    */
    Route::prefix('{menuId}/items')->name('items.')->group(function () {
        // メニューアイテム一覧取得（Ajax）
        Route::get('/', [AdminMenuItemController::class, 'getItems'])->name('index');

        // メニューアイテム一括保存（Ajax）
        Route::post('/sync', [AdminMenuItemController::class, 'syncItems'])->name('sync');

        // メニューアイテム作成
        Route::get('/create', [AdminMenuItemController::class, 'create'])->name('create');
        Route::post('/', [AdminMenuItemController::class, 'store'])->name('store');

        // 親アイテム配下に作成
        Route::get('/create/{parentId}', [AdminMenuItemController::class, 'create'])->name('create.child');

        // 並び順更新（Ajax）
        Route::post('/order', [AdminMenuItemController::class, 'updateOrder'])->name('order');
    });

    /*
    |--------------------------------------------------------------------------
    | リンクソースAPI
    |--------------------------------------------------------------------------
    */
    Route::prefix('link-sources')->name('link-sources.')->group(function () {
        Route::get('/', [AdminMenuLinkSourceController::class, 'index'])->name('index');
        Route::get('/{sourceType}/items', [AdminMenuLinkSourceController::class, 'items'])->name('items');
    });

    /*
    |--------------------------------------------------------------------------
    | メニューアイテム管理（単体操作）
    |--------------------------------------------------------------------------
    */
    Route::prefix('items')->name('items.')->group(function () {
        // メニューアイテム編集
        Route::get('/edit/{id}', [AdminMenuItemController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminMenuItemController::class, 'update'])->name('update');

        // メニューアイテム削除
        Route::delete('/{id}', [AdminMenuItemController::class, 'destroy'])->name('destroy');

        // メニューアイテム移動（Ajax）
        Route::post('/{id}/move', [AdminMenuItemController::class, 'move'])->name('move');
    });
});
