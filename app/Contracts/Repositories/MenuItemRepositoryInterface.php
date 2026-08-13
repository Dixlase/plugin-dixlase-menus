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

namespace Plugins\DixlaseMenus\App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Plugins\DixlaseMenus\App\Models\MenuItem;

/**
 * メニューアイテムリポジトリインターフェース
 */
interface MenuItemRepositoryInterface
{
    /**
     * IDでメニューアイテムを取得
     */
    public function find(int $id): ?MenuItem;

    /**
     * IDでメニューアイテムを取得（見つからない場合は例外）
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): MenuItem;

    /**
     * 特定のメニューのアイテムを取得
     */
    public function getByMenuId(int $menuId): Collection;

    /**
     * 特定のメニューのルートアイテムを取得
     */
    public function getRootItems(int $menuId): Collection;

    /**
     * 特定のメニューの有効なアイテムを取得
     */
    public function getActiveItems(int $menuId): Collection;

    /**
     * 特定の親の子アイテムを取得
     */
    public function getChildren(int $parentId): Collection;

    /**
     * 階層構造でアイテムを取得
     *
     * @param  int  $depth  取得する階層の深さ
     */
    public function getHierarchy(int $menuId, int $depth = 3): Collection;

    /**
     * メニューアイテムを作成
     */
    public function create(array $data): MenuItem;

    /**
     * メニューアイテムを更新
     */
    public function update(int $id, array $data): MenuItem;

    /**
     * メニューアイテムを削除
     */
    public function delete(int $id): bool;

    /**
     * メニューアイテムと子孫を削除
     */
    public function deleteWithDescendants(int $id): bool;

    /**
     * メニューアイテムを完全に削除（復元不可）
     *
     * delete() はソフト削除。こちらは行ごと消す。
     */
    public function forceDelete(int $id): bool;

    /**
     * ソフト削除されたメニューアイテムを復元
     */
    public function restore(int $id): bool;

    /**
     * 表示順を更新
     *
     * @param  array  $items  [['id' => 1, 'order' => 1], ...]
     */
    public function updateOrder(array $items): bool;

    /**
     * 親を変更
     */
    public function moveToParent(int $id, ?int $newParentId): MenuItem;

    /**
     * 深さを再計算
     */
    public function recalculateDepth(int $menuId): void;

    /**
     * 特定のソースタイプのアイテムを取得
     */
    public function getBySource(string $sourceType, string|int $sourceId): Collection;

    /**
     * メニューアイテムが存在するかチェック
     */
    public function exists(int $id): bool;

    /**
     * 子アイテムを持つかチェック
     */
    public function hasChildren(int $id): bool;
}
