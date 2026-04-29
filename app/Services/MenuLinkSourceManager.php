<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc.
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

namespace Plugins\DixlaseMenus\App\Services;

use Plugins\DixlaseMenus\App\Contracts\MenuLinkSource;
use Plugins\DixlaseMenus\App\Services\MenuLinkSources\LinkableProviderAdapter;
use Illuminate\Support\Collection;

/**
 * メニューリンクソース管理サービス
 * 
 * 様々なリンクソース（ページ、カテゴリ、カスタムURLなど）を
 * 登録・管理するサービスクラス
 */
class MenuLinkSourceManager
{
    /**
     * 登録されたリンクソース
     * 
     * @var array<string, MenuLinkSource>
     */
    protected array $sources = [];

    /**
     * リンクソースを登録
     * 
     * @param MenuLinkSource $source リンクソース
     * @return void
     */
    public function register(MenuLinkSource $source): void
    {
        $this->sources[$source->getSourceType()] = $source;
    }

    /**
     * 複数のリンクソースを一括登録
     * 
     * @param array<MenuLinkSource> $sources リンクソースの配列
     * @return void
     */
    public function registerMultiple(array $sources): void
    {
        foreach ($sources as $source) {
            $this->register($source);
        }
    }

    /**
     * 特定のリンクソースを取得
     * 
     * @param string $sourceType ソースタイプ
     * @return MenuLinkSource|null
     */
    public function getSource(string $sourceType): ?MenuLinkSource
    {
        return $this->sources[$sourceType] ?? null;
    }

    /**
     * すべてのリンクソースを取得
     * 
     * @return Collection<string, MenuLinkSource>
     */
    public function getAllSources(): Collection
    {
        return collect($this->sources);
    }

    /**
     * 利用可能なリンクソースのみを取得
     * 
     * @return Collection<string, MenuLinkSource>
     */
    public function getAvailableSources(): Collection
    {
        return collect($this->sources)
            ->filter(fn(MenuLinkSource $source) => $source->isAvailable());
    }

    /**
     * リンクソースが登録されているか確認
     * 
     * @param string $sourceType ソースタイプ
     * @return bool
     */
    public function hasSource(string $sourceType): bool
    {
        return isset($this->sources[$sourceType]);
    }

    /**
     * リンクソースの登録を解除
     * 
     * @param string $sourceType ソースタイプ
     * @return void
     */
    public function unregister(string $sourceType): void
    {
        unset($this->sources[$sourceType]);
    }

    /**
     * すべてのリンクソースをクリア
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->sources = [];
    }

    /**
     * リンクソースの選択肢を取得（セレクトボックス用）
     * 
     * @return array<string, string> ['source_type' => 'Source Label']
     */
    public function getSourceOptions(): array
    {
        return $this->getAvailableSources()
            ->mapWithKeys(fn(MenuLinkSource $source) => [
                $source->getSourceType() => $source->getSourceLabel()
            ])
            ->toArray();
    }

    /**
     * 特定のソースからアイテムを検索
     * 
     * @param string $sourceType ソースタイプ
     * @param string $query 検索クエリ
     * @param int $limit 取得件数の上限
     * @return array
     */
    public function searchItems(string $sourceType, string $query, int $limit = 20): array
    {
        $source = $this->getSource($sourceType);
        
        if (!$source || !$source->isAvailable()) {
            return [];
        }

        return $source->searchItems($query, $limit);
    }

    /**
     * 特定のソースから利用可能なアイテムを取得
     * 
     * @param string $sourceType ソースタイプ
     * @return array
     */
    public function getAvailableItems(string $sourceType): array
    {
        $source = $this->getSource($sourceType);
        
        if (!$source || !$source->isAvailable()) {
            return [];
        }

        return $source->getAvailableItems();
    }

    /**
     * 利用可能なリンクソースのメタデータ一覧を取得
     *
     * @return array<int, array{type: string, label: string, icon: string, hasItems: bool}>
     */
    public function getSourcesMetadata(): array
    {
        return $this->getAvailableSources()
            ->map(function (MenuLinkSource $source) {
                $icon = ($source instanceof LinkableProviderAdapter)
                    ? ($source->getIcon() ?? 'fas fa-link')
                    : 'fas fa-link';

                return [
                    'type' => $source->getSourceType(),
                    'label' => $source->getSourceLabel(),
                    'icon' => $icon,
                    'hasItems' => $source->getSourceType() !== 'custom_url',
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * 登録されているソース数を取得
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->sources);
    }
}
