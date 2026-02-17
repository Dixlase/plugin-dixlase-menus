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

namespace Plugins\DixlaseMenu\App\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Plugins\DixlaseMenu\App\Models\MenuItem;
use Plugins\DixlaseMenu\App\Services\MenuService;

/**
 * メニューアイテムモデルのオブザーバー
 *
 * メニューアイテムの作成・更新・削除時に親メニューのキャッシュを自動破棄する
 */
class MenuItemObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private MenuService $menuService
    ) {}

    /**
     * メニューアイテム作成後のキャッシュクリア
     */
    public function created(MenuItem $menuItem): void
    {
        $this->menuService->clearMenuCache($menuItem->menu_id);
    }

    /**
     * メニューアイテム更新後のキャッシュクリア
     */
    public function updated(MenuItem $menuItem): void
    {
        $this->menuService->clearMenuCache($menuItem->menu_id);
    }

    /**
     * メニューアイテム削除後のキャッシュクリア
     */
    public function deleted(MenuItem $menuItem): void
    {
        $this->menuService->clearMenuCache($menuItem->menu_id);
    }

    /**
     * メニューアイテム完全削除後のキャッシュクリア
     */
    public function forceDeleted(MenuItem $menuItem): void
    {
        $this->menuService->clearMenuCache($menuItem->menu_id);
    }
}
