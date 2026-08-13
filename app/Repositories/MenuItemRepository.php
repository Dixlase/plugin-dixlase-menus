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

namespace Plugins\DixlaseMenus\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuItemRepositoryInterface;
use Plugins\DixlaseMenus\App\Models\MenuItem;

/**
 * メニューアイテムリポジトリ実装
 */
class MenuItemRepository implements MenuItemRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?MenuItem
    {
        return MenuItem::find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail(int $id): MenuItem
    {
        return MenuItem::findOrFail($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getByMenuId(int $menuId): Collection
    {
        return MenuItem::where('menu_id', $menuId)
            ->ordered()
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getRootItems(int $menuId): Collection
    {
        return MenuItem::where('menu_id', $menuId)
            ->root()
            ->ordered()
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveItems(int $menuId): Collection
    {
        return MenuItem::where('menu_id', $menuId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(int $parentId): Collection
    {
        return MenuItem::where('parent_id', $parentId)
            ->ordered()
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getHierarchy(int $menuId, int $depth = 3): Collection
    {
        $with = $this->buildHierarchyRelations($depth);

        return MenuItem::where('menu_id', $menuId)
            ->root()
            ->with($with)
            ->ordered()
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): MenuItem
    {
        // 親が指定されている場合、深さを自動計算
        if (isset($data['parent_id']) && $data['parent_id']) {
            $parent = $this->find($data['parent_id']);
            $data['depth'] = $parent ? $parent->depth + 1 : 0;
        }

        return MenuItem::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): MenuItem
    {
        $item = $this->findOrFail($id);

        // 親が変更された場合、深さを再計算
        if (isset($data['parent_id']) && $data['parent_id'] !== $item->parent_id) {
            if ($data['parent_id']) {
                $parent = $this->find($data['parent_id']);
                $data['depth'] = $parent ? $parent->depth + 1 : 0;
            } else {
                $data['depth'] = 0;
            }
        }

        $item->update($data);

        return $item->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $item = $this->findOrFail($id);

        // Soft delete. MenuItem uses SoftDeletes; calling forceDelete() here
        // stepped straight past it, so removing an item from a menu destroyed
        // the row with no way back.
        return $item->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function forceDelete(int $id): bool
    {
        $item = MenuItem::withTrashed()->findOrFail($id);

        return $item->forceDelete();
    }

    /**
     * {@inheritDoc}
     */
    public function restore(int $id): bool
    {
        $item = MenuItem::withTrashed()->findOrFail($id);

        return $item->restore();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteWithDescendants(int $id): bool
    {
        $item = $this->findOrFail($id);

        DB::beginTransaction();
        try {
            // すべての子孫を取得して削除
            $descendants = $item->descendants();
            foreach ($descendants as $descendant) {
                $descendant->delete();
            }

            // 自身を削除
            $result = $item->delete();

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function updateOrder(array $items): bool
    {
        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                MenuItem::where('id', $item['id'])
                    ->update(['display_order' => $item['order']]);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function moveToParent(int $id, ?int $newParentId): MenuItem
    {
        $item = $this->findOrFail($id);

        // 新しい深さを計算
        if ($newParentId) {
            $newParent = $this->find($newParentId);
            $newDepth = $newParent ? $newParent->depth + 1 : 0;
        } else {
            $newDepth = 0;
        }

        DB::beginTransaction();
        try {
            // アイテムを移動
            $item->update([
                'parent_id' => $newParentId,
                'depth' => $newDepth,
            ]);

            // 子孫の深さも再計算
            $this->recalculateDescendantsDepth($item);

            DB::commit();

            return $item->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function recalculateDepth(int $menuId): void
    {
        $rootItems = $this->getRootItems($menuId);

        foreach ($rootItems as $item) {
            $this->recalculateItemDepth($item, 0);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getBySource(string $sourceType, string|int $sourceId): Collection
    {
        return MenuItem::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function exists(int $id): bool
    {
        return MenuItem::where('id', $id)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function hasChildren(int $id): bool
    {
        return MenuItem::where('parent_id', $id)->exists();
    }

    /**
     * 階層構造のリレーション文字列を構築
     */
    protected function buildHierarchyRelations(int $depth): array
    {
        $relations = [];

        for ($i = 1; $i < $depth; $i++) {
            $relations[] = str_repeat('children.', $i - 1).'children';
        }

        return $relations;
    }

    /**
     * アイテムとその子孫の深さを再計算
     */
    protected function recalculateDescendantsDepth(MenuItem $item): void
    {
        $children = $item->children;

        foreach ($children as $child) {
            $child->update(['depth' => $item->depth + 1]);
            $this->recalculateDescendantsDepth($child);
        }
    }

    /**
     * アイテムの深さを再帰的に再計算
     */
    protected function recalculateItemDepth(MenuItem $item, int $depth): void
    {
        $item->update(['depth' => $depth]);

        $children = $item->children;
        foreach ($children as $child) {
            $this->recalculateItemDepth($child, $depth + 1);
        }
    }

    /**
     * メニューのすべてのアイテムを削除
     */
    public function deleteByMenuId(int $menuId): bool
    {
        return MenuItem::where('menu_id', $menuId)->delete() > 0;
    }

    /**
     * メニューアイテムを一括同期（既存を削除して新規作成）
     */
    public function syncItems(int $menuId, array $items): Collection
    {
        DB::beginTransaction();
        try {
            // 既存のアイテムを削除
            $this->deleteByMenuId($menuId);

            // 新しいアイテムを作成
            $createdItems = $this->createItemsRecursively($menuId, $items, null, 0);

            DB::commit();

            // 階層構造で取得して返す
            return $this->getHierarchy($menuId);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * メニューアイテムを再帰的に作成
     */
    protected function createItemsRecursively(int $menuId, array $items, ?int $parentId, int $depth): array
    {
        $created = [];
        $order = 0;

        foreach ($items as $itemData) {
            // 空のラベルはスキップ
            $label = $itemData['label'] ?? $itemData['title'] ?? '';
            if (empty($label)) {
                continue;
            }

            $item = MenuItem::create([
                'menu_id' => $menuId,
                'parent_id' => $parentId,
                'title' => $label,
                'url' => $itemData['url'] ?? '',
                'source_type' => $itemData['source_type'] ?? 'custom_url',
                'source_id' => $itemData['source_id'] ?? null,
                'target' => $itemData['target'] ?? '_self',
                'css_class' => $itemData['css_class'] ?? null,
                'icon_class' => $itemData['icon_class'] ?? null,
                'depth' => $depth,
                'display_order' => $order++,
                'is_active' => true,
                'is_visible' => true,
            ]);

            $created[] = $item;

            // 子アイテムがある場合は再帰的に作成
            if (! empty($itemData['children']) && is_array($itemData['children'])) {
                $this->createItemsRecursively($menuId, $itemData['children'], $item->id, $depth + 1);
            }
        }

        return $created;
    }
}
