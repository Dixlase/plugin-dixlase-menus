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
use Plugins\DixlaseMenu\App\Models\Menu;
use Plugins\DixlaseMenu\App\Services\MenuService;

/**
 * メニューモデルのオブザーバー
 *
 * メニューの作成・更新・削除・復元時にキャッシュを自動破棄する
 */
class MenuObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private MenuService $menuService
    ) {}

    /**
     * メニュー作成後のキャッシュクリア
     */
    public function created(Menu $menu): void
    {
        $this->menuService->clearMenuCache($menu->id);
    }

    /**
     * メニュー更新後のキャッシュクリア
     */
    public function updated(Menu $menu): void
    {
        $this->menuService->clearMenuCache($menu->id);
    }

    /**
     * メニュー削除後のキャッシュクリア
     */
    public function deleted(Menu $menu): void
    {
        $this->menuService->clearMenuCache($menu->id);
    }

    /**
     * メニュー復元後のキャッシュクリア
     */
    public function restored(Menu $menu): void
    {
        $this->menuService->clearMenuCache($menu->id);
    }

    /**
     * メニュー完全削除後のキャッシュクリア
     */
    public function forceDeleted(Menu $menu): void
    {
        $this->menuService->clearMenuCache($menu->id);
    }
}
