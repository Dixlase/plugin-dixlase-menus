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

namespace Plugins\DixlaseMenus\App\Contracts;

/**
 * メニューリンクソース契約
 * 
 * この契約を実装することで、様々なソース（ページ、カテゴリ、カスタムURLなど）から
 * メニューリンクを生成できるようになります。
 */
interface MenuLinkSource
{
    /**
     * リンクソースの一意な識別子を取得
     * 
     * @return string 例: 'page', 'category', 'custom_url', 'post'
     */
    public function getSourceType(): string;

    /**
     * リンクソースの表示名を取得
     * 
     * @return string 例: 'ページ', 'カテゴリ', 'カスタムURL'
     */
    public function getSourceLabel(): string;

    /**
     * 利用可能なリンクアイテムのリストを取得
     * 
     * @return array<int, array{id: int|string, title: string, url: string, description?: string}>
     * 例: [
     *   ['id' => 1, 'title' => 'ホーム', 'url' => '/', 'description' => 'トップページ'],
     *   ['id' => 2, 'title' => '会社概要', 'url' => '/about', 'description' => '会社情報'],
     * ]
     */
    public function getAvailableItems(): array;

    /**
     * 特定のアイテムIDからリンク情報を取得
     * 
     * @param int|string $itemId アイテムID
     * @return array{id: int|string, title: string, url: string, description?: string}|null
     */
    public function getItemById(int|string $itemId): ?array;

    /**
     * このソースが現在利用可能かどうかを確認
     * 
     * @return bool 例: ページプラグインがインストールされていない場合はfalse
     */
    public function isAvailable(): bool;

    /**
     * 検索クエリに基づいてアイテムを検索
     * 
     * @param string $query 検索クエリ
     * @param int $limit 取得件数の上限（デフォルト: 20）
     * @return array<int, array{id: int|string, title: string, url: string, description?: string}>
     */
    public function searchItems(string $query, int $limit = 20): array;

    /**
     * アイテムのURLを生成
     * 
     * @param int|string $itemId アイテムID
     * @return string|null URLまたはnull（アイテムが存在しない場合）
     */
    public function generateUrl(int|string $itemId): ?string;

    /**
     * アイテムが公開状態かどうかを確認
     * 
     * @param int|string $itemId アイテムID
     * @return bool 公開されている場合true
     */
    public function isPublished(int|string $itemId): bool;
}
