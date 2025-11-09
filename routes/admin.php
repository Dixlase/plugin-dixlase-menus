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
use Plugins\DixlaseMenu\App\Http\Controllers\Admin\AdminMenuController;
use Plugins\DixlaseMenu\App\Http\Controllers\Admin\AdminMenuItemController;
use Plugins\DixlaseMenu\App\Http\Controllers\Admin\AdminMenuSettingsController;

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

/*
|--------------------------------------------------------------------------
| メニュー管理
|--------------------------------------------------------------------------
*/
Route::prefix('menus')->name('dixlase-menu::admin.menus.')->group(function () {
    // メニュー一覧
    Route::get('/', [AdminMenuController::class, 'index'])->name('index');
    
    // メニュー作成
    Route::get('/create', [AdminMenuController::class, 'create'])->name('create');
    Route::post('/', [AdminMenuController::class, 'store'])->name('store');
    
    // メニュー編集
    Route::get('/{id}/edit', [AdminMenuController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminMenuController::class, 'update'])->name('update');
    
    // メニュー削除
    Route::get('/{id}/delete', [AdminMenuController::class, 'delete'])->name('delete');
    Route::delete('/{id}', [AdminMenuController::class, 'destroy'])->name('destroy');
    
    // メニュー復元
    Route::post('/{id}/restore', [AdminMenuController::class, 'restore'])->name('restore');
    
    // キャッシュクリア
    Route::post('/cache/clear', [AdminMenuController::class, 'clearCache'])->name('cache.clear');
});

/*
|--------------------------------------------------------------------------
| メニューアイテム管理
|--------------------------------------------------------------------------
*/
Route::prefix('menus/{menuId}/items')->name('dixlase-menu::admin.menus.items.')->group(function () {
    // メニューアイテム作成
    Route::get('/create', [AdminMenuItemController::class, 'create'])->name('create');
    Route::post('/', [AdminMenuItemController::class, 'store'])->name('store');
    
    // 親アイテム配下に作成
    Route::get('/create/{parentId}', [AdminMenuItemController::class, 'create'])->name('create.child');
    
    // 並び順更新（Ajax）
    Route::post('/order', [AdminMenuItemController::class, 'updateOrder'])->name('order');
});

Route::prefix('menu-items')->name('dixlase-menu::admin.menu-items.')->group(function () {
    // メニューアイテム編集
    Route::get('/{id}/edit', [AdminMenuItemController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminMenuItemController::class, 'update'])->name('update');
    
    // メニューアイテム削除
    Route::delete('/{id}', [AdminMenuItemController::class, 'destroy'])->name('destroy');
    
    // メニューアイテム移動（Ajax）
    Route::post('/{id}/move', [AdminMenuItemController::class, 'move'])->name('move');
});

/*
|--------------------------------------------------------------------------
| メニュープラグイン設定
|--------------------------------------------------------------------------
*/
Route::prefix('settings')->name('dixlase-menu::admin.settings.')->group(function () {
    // 設定画面
    Route::get('/menus', [AdminMenuSettingsController::class, 'index'])->name('menus.index');
    Route::put('/menus', [AdminMenuSettingsController::class, 'update'])->name('menus.update');
    
    // 設定キャッシュクリア
    Route::post('/menus/cache/clear', [AdminMenuSettingsController::class, 'clearCache'])->name('menus.cache.clear');
});
