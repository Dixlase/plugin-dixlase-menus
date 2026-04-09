<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc.
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

namespace Plugins\DixlaseMenus\App\Contracts\Repositories;

use Plugins\DixlaseMenus\App\Models\MenuSetting;

/**
 * メニュー設定リポジトリインターフェース
 */
interface MenuSettingRepositoryInterface
{
    /**
     * 設定値を取得（型変換あり）
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * 設定値を保存
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @return MenuSetting
     */
    public function set(string $key, $value, string $type = 'string', ?string $description = null): MenuSetting;

    /**
     * 複数の設定を一括取得
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function getMultiple(array $keys): array;

    /**
     * 複数の設定を一括保存
     *
     * @param array $settings [['key' => 'value', 'type' => 'string'], ...]
     * @return bool
     */
    public function setMultiple(array $settings): bool;

    /**
     * すべての設定を取得
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * 設定が存在するかチェック
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * 設定を削除
     *
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool;

    /**
     * 特定の型の設定を取得
     *
     * @param string $type
     * @return array<string, mixed>
     */
    public function getByType(string $type): array;

    /**
     * boolean型の設定を取得
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function getBoolean(string $key, bool $default = false): bool;

    /**
     * integer型の設定を取得
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getInteger(string $key, int $default = 0): int;

    /**
     * JSON型の設定を取得
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getJson(string $key, array $default = []): array;

    /**
     * キャッシュ付きで設定を取得
     *
     * @param string $key
     * @param mixed $default
     * @param int $ttl キャッシュ有効期間（秒）
     * @return mixed
     */
    public function getCached(string $key, $default = null, int $ttl = 3600);

    /**
     * 設定のキャッシュをクリア
     *
     * @param string|null $key 特定のキーまたはnullで全クリア
     * @return void
     */
    public function clearCache(?string $key = null): void;
}
