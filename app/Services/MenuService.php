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

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuItemRepositoryInterface;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenus\App\Models\Menu;
use Plugins\DixlaseMenus\App\Models\MenuItem;

/**
 * メニュー操作サービス
 */
class MenuService
{
    /**
     * キャッシュキーのプレフィックス
     */
    protected const CACHE_PREFIX = 'dixlase_menu:';

    /**
     * キャッシュTTL（秒）
     */
    protected const CACHE_TTL = 3600;

    public function __construct(
        protected MenuRepositoryInterface $menuRepository,
        protected MenuItemRepositoryInterface $menuItemRepository
    ) {}

    /**
     * メニューアイテムを一括保存
     *
     * フロントエンドから送信されたメニューアイテムの配列を
     * DBに保存します。既存のアイテムは更新、新規は作成、
     * 送信されなかったアイテムは削除されます。
     */
    public function syncMenuItems(int $menuId, array $items): bool
    {
        DB::beginTransaction();
        try {
            // 現在のアイテムIDを取得
            $existingIds = MenuItem::where('menu_id', $menuId)->pluck('id')->toArray();
            $processedIds = [];

            // アイテムを再帰的に処理
            $this->processItems($menuId, $items, null, 0, $processedIds);

            // 送信されなかったアイテムを削除
            $idsToDelete = array_diff($existingIds, $processedIds);
            if (! empty($idsToDelete)) {
                MenuItem::whereIn('id', $idsToDelete)->delete();
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MenuService: Failed to sync menu items', [
                'menu_id' => $menuId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * メニューアイテムを再帰的に処理
     */
    protected function processItems(
        int $menuId,
        array $items,
        ?int $parentId,
        int $depth,
        array &$processedIds
    ): void {
        foreach ($items as $order => $itemData) {
            $item = $this->saveItem($menuId, $itemData, $parentId, $depth, $order);
            $processedIds[] = $item->id;

            // 子アイテムを処理
            if (! empty($itemData['children']) && is_array($itemData['children'])) {
                $this->processItems($menuId, $itemData['children'], $item->id, $depth + 1, $processedIds);
            }
        }
    }

    /**
     * 単一のメニューアイテムを保存
     */
    protected function saveItem(
        int $menuId,
        array $data,
        ?int $parentId,
        int $depth,
        int $order
    ): MenuItem {
        $itemData = [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $data['label'] ?? $data['title'] ?? '',
            'url' => ($data['source_type'] ?? '') === 'menu_group' ? null : ($data['url'] ?? ''),
            'source_type' => $data['source_type'] ?? 'custom_url',
            'source_id' => $data['source_id'] ?? null,
            'target' => $data['target'] ?? '_self',
            'css_class' => $data['css_class'] ?? null,
            'icon_class' => $data['icon_class'] ?? null,
            'description' => $data['description'] ?? null,
            'depth' => $depth,
            'display_order' => $order,
            'is_active' => $data['is_active'] ?? true,
            'is_visible' => $data['is_visible'] ?? true,
            'visibility_conditions' => $data['visibility_conditions'] ?? null,
        ];

        // 既存のアイテムを更新、または新規作成
        $id = $data['id'] ?? null;

        // IDが数値でない場合（フロントエンドで生成した一時ID）は新規作成
        if ($id && is_numeric($id)) {
            $existingItem = MenuItem::find($id);
            if ($existingItem && $existingItem->menu_id === $menuId) {
                $existingItem->update($itemData);

                return $existingItem;
            }
        }

        return MenuItem::create($itemData);
    }

    /**
     * メニューを階層構造で取得
     */
    public function getMenuHierarchy(int $menuId, bool $activeOnly = false): array
    {
        $cacheKey = self::CACHE_PREFIX."hierarchy:{$menuId}:".($activeOnly ? '1' : '0');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($menuId, $activeOnly) {
            $query = MenuItem::where('menu_id', $menuId)
                ->whereNull('parent_id')
                ->orderBy('display_order');

            if ($activeOnly) {
                $query->where('is_active', true)->where('is_visible', true);
            }

            $rootItems = $query->get();

            return $this->buildHierarchy($rootItems, $activeOnly);
        });
    }

    /**
     * 階層構造を構築
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $items
     */
    protected function buildHierarchy($items, bool $activeOnly = false): array
    {
        $result = [];

        foreach ($items as $item) {
            $itemArray = $this->itemToArray($item);

            // 子アイテムを取得
            $childQuery = $item->children()->orderBy('display_order');
            if ($activeOnly) {
                $childQuery->where('is_active', true)->where('is_visible', true);
            }
            $children = $childQuery->get();

            if ($children->isNotEmpty()) {
                $itemArray['children'] = $this->buildHierarchy($children, $activeOnly);
            }

            $result[] = $itemArray;
        }

        return $result;
    }

    /**
     * MenuItemを配列に変換
     */
    protected function itemToArray(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'label' => $item->title,
            'title' => $item->title,
            'url' => $item->url,
            'target' => $item->target,
            'source_type' => $item->source_type,
            'source_id' => $item->source_id,
            'css_class' => $item->css_class,
            'icon_class' => $item->icon_class,
            'description' => $item->description,
            'depth' => $item->depth,
            'is_active' => $item->is_active,
            'is_visible' => $item->is_visible,
            'children' => [],
        ];
    }

    /**
     * スラッグでメニューを取得
     */
    public function getMenuBySlug(string $slug, bool $activeOnly = true): ?array
    {
        $cacheKey = self::CACHE_PREFIX."slug:{$slug}:".($activeOnly ? '1' : '0');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($slug, $activeOnly) {
            $query = Menu::where('slug', $slug);

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            $menu = $query->first();

            if (! $menu) {
                return null;
            }

            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'location' => $menu->location,
                'description' => $menu->description,
                'items' => $this->getMenuHierarchy($menu->id, $activeOnly),
            ];
        });
    }

    /**
     * ロケーションでメニューを取得
     */
    public function getMenuByLocation(string $location, bool $activeOnly = true): ?array
    {
        $cacheKey = self::CACHE_PREFIX."location:{$location}:".($activeOnly ? '1' : '0');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($location, $activeOnly) {
            $query = Menu::where('location', $location);

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            $menu = $query->orderBy('display_order')->first();

            if (! $menu) {
                return null;
            }

            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'location' => $menu->location,
                'description' => $menu->description,
                'items' => $this->getMenuHierarchy($menu->id, $activeOnly),
            ];
        });
    }

    /**
     * デフォルトメニューを取得（最初に作成されたアクティブなメニュー）
     */
    public function getDefaultMenu(): ?array
    {
        $cacheKey = self::CACHE_PREFIX.'default';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $menu = Menu::where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('id')
                ->first();

            if (! $menu) {
                return null;
            }

            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'location' => $menu->location,
                'description' => $menu->description,
                'items' => $this->getMenuHierarchy($menu->id, true),
            ];
        });
    }

    /**
     * メニューのキャッシュをクリア
     */
    public function clearMenuCache(?int $menuId = null): void
    {
        if ($menuId) {
            $menu = Menu::withTrashed()->find($menuId);
            if ($menu) {
                Cache::forget(self::CACHE_PREFIX."hierarchy:{$menuId}:0");
                Cache::forget(self::CACHE_PREFIX."hierarchy:{$menuId}:1");
                Cache::forget(self::CACHE_PREFIX."slug:{$menu->slug}:0");
                Cache::forget(self::CACHE_PREFIX."slug:{$menu->slug}:1");
                if ($menu->location) {
                    Cache::forget(self::CACHE_PREFIX."location:{$menu->location}:0");
                    Cache::forget(self::CACHE_PREFIX."location:{$menu->location}:1");
                }

                // リポジトリ層のキャッシュもクリア
                $this->menuRepository->clearCache($menu->slug);
            }
        }

        Cache::forget(self::CACHE_PREFIX.'default');
        $this->menuRepository->clearAllCache();
    }

    /**
     * すべてのメニューキャッシュをクリア
     */
    public function clearAllCache(): void
    {
        $menus = Menu::all();
        foreach ($menus as $menu) {
            $this->clearMenuCache($menu->id);
        }
    }
}
