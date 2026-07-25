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

namespace Plugins\DixlaseMenus\App\Services\MenuLinkSources;

use Plugins\DixlaseMenus\App\Contracts\MenuLinkSource;

/**
 * カスタムURLリンクソース
 * 
 * ユーザーが自由にURLとタイトルを指定できるリンクソース
 */
class CustomUrlSource implements MenuLinkSource
{
    /**
     * {@inheritDoc}
     */
    public function getSourceType(): string
    {
        return 'custom_url';
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceLabel(): string
    {
        return __('dixlase-menus::menu.link_sources.custom_url');
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableItems(): array
    {
        // カスタムURLは動的に作成されるため、事前定義されたアイテムはない
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getItemById(int|string $itemId): ?array
    {
        // カスタムURLはIDベースの取得をサポートしない
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable(): bool
    {
        // カスタムURLは常に利用可能
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function searchItems(string $query, int $limit = 20): array
    {
        // カスタムURLは検索をサポートしない
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function generateUrl(int|string $itemId): ?string
    {
        // カスタムURLはIDベースのURL生成をサポートしない
        // URLは直接メニューアイテムに保存される
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isPublished(int|string $itemId): bool
    {
        // カスタムURLは常に公開状態
        return true;
    }

    /**
     * カスタムURLリンクを作成
     * 
     * @param string $title リンクタイトル
     * @param string $url リンクURL
     * @param string|null $description リンクの説明（オプション）
     * @return array{id: string, title: string, url: string, description?: string}
     */
    public function createCustomLink(string $title, string $url, ?string $description = null): array
    {
        $link = [
            'id' => 'custom_' . md5($url),
            'title' => $title,
            'url' => $url,
        ];

        if ($description) {
            $link['description'] = $description;
        }

        return $link;
    }

    /**
     * URLが有効かどうかを検証
     * 
     * @param string $url 検証するURL
     * @return bool 有効な場合true
     */
    public function validateUrl(string $url): bool
    {
        // 相対URLまたは絶対URLを許可
        if (str_starts_with($url, '/')) {
            return true;
        }

        // 完全なURLの場合はfilter_varで検証
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
