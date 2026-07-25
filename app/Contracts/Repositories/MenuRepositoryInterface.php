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

use Plugins\DixlaseMenus\App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

/**
 * メニューリポジトリインターフェース
 */
interface MenuRepositoryInterface
{
    /**
     * すべてのメニューを取得
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * 有効なメニューのみ取得
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * IDでメニューを取得
     *
     * @param int $id
     * @return Menu|null
     */
    public function find(int $id): ?Menu;

    /**
     * IDでメニューを取得（見つからない場合は例外）
     *
     * @param int $id
     * @return Menu
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Menu;

    /**
     * スラッグでメニューを取得
     *
     * @param string $slug
     * @return Menu|null
     */
    public function findBySlug(string $slug): ?Menu;

    /**
     * 特定の位置のメニューを取得
     *
     * @param string $location
     * @return Collection
     */
    public function getByLocation(string $location): Collection;

    /**
     * 特定の位置の有効なメニューを取得
     *
     * @param string $location
     * @return Menu|null
     */
    public function getActiveByLocation(string $location): ?Menu;

    /**
     * メニューを作成
     *
     * @param array $data
     * @return Menu
     */
    public function create(array $data): Menu;

    /**
     * メニューを更新
     *
     * @param int $id
     * @param array $data
     * @return Menu
     */
    public function update(int $id, array $data): Menu;

    /**
     * メニューを削除
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * メニューをソフトデリート
     *
     * @param int $id
     * @return bool
     */
    public function softDelete(int $id): bool;

    /**
     * メニューを復元
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool;

    /**
     * メニューをアイテムと一緒に取得
     *
     * @param int $id
     * @return Menu|null
     */
    public function findWithItems(int $id): ?Menu;

    /**
     * メニューをアクティブなアイテムと一緒に取得
     *
     * @param int $id
     * @return Menu|null
     */
    public function findWithActiveItems(int $id): ?Menu;

    /**
     * 階層構造を持つメニューを取得
     *
     * @param int $id
     * @param int $depth 取得する階層の深さ
     * @return Menu|null
     */
    public function findWithHierarchy(int $id, int $depth = 3): ?Menu;

    /**
     * キャッシュ付きでメニューを取得
     *
     * @param string $slug
     * @param int $ttl キャッシュ有効期間（秒）
     * @return Menu|null
     */
    public function getCached(string $slug, int $ttl = 3600): ?Menu;

    /**
     * メニューのキャッシュをクリア
     *
     * @param string $slug
     * @return void
     */
    public function clearCache(string $slug): void;

    /**
     * すべてのメニューキャッシュをクリア
     *
     * @return void
     */
    public function clearAllCache(): void;

    /**
     * メニューが存在するかチェック
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * スラッグが存在するかチェック
     *
     * @param string $slug
     * @param int|null $excludeId 除外するID
     * @return bool
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;
}
