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

use App\Contracts\PluginIntegration\MenuProviderInterface;
use App\DTO\PluginIntegration\MenuDTO;
use App\DTO\PluginIntegration\MenuItemDTO;
use Plugins\DixlaseMenus\App\Models\Menu;

/**
 * Provides menu data via core MenuProviderInterface
 *
 * Translates DixlaseMenus plugin models into core DTOs
 * so themes can access menu data without referencing plugin internals.
 */
class DixlaseMenusMenuProvider implements MenuProviderInterface
{
    public function getPluginSlug(): string
    {
        return 'dixlase-menus';
    }

    public function isCapabilityAvailable(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('plg_dixlase_menus');
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @return array<int|string, string>
     */
    public function getMenuOptions(): array
    {
        try {
            return Menu::query()
                ->active()
                ->ordered()
                ->pluck('name', 'id')
                ->toArray();
        } catch (\Exception) {
            return [];
        }
    }

    public function getMenu(int|string $menuId): ?MenuDTO
    {
        try {
            $menu = Menu::query()
                ->active()
                ->with(['activeItems' => fn ($q) => $q->orderBy('display_order')])
                ->find($menuId);

            if (! $menu) {
                return null;
            }

            return $this->toDTO($menu);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @return MenuDTO[]
     */
    public function getMenus(): array
    {
        try {
            $menus = Menu::query()
                ->active()
                ->ordered()
                ->with(['activeItems' => fn ($q) => $q->orderBy('display_order')])
                ->get();

            return $menus->map(fn (Menu $menu) => $this->toDTO($menu))->toArray();
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Menuモデル → MenuDTO に変換
     *
     * 全アクティブ項目を1クエリで取得し、PHPでツリー構築（N+1回避）
     */
    private function toDTO(Menu $menu): MenuDTO
    {
        $allItems = $menu->activeItems;
        $roots = $allItems->whereNull('parent_id')->sortBy('display_order');

        /** @var array<int, \Illuminate\Support\Collection> $childrenByParent */
        $childrenByParent = $allItems->whereNotNull('parent_id')
            ->sortBy('display_order')
            ->groupBy('parent_id');

        $items = $roots->map(fn ($item) => $this->buildItemDTO($item, $childrenByParent))->values()->toArray();

        return new MenuDTO(
            id: $menu->id,
            name: $menu->name,
            slug: $menu->slug,
            items: $items,
        );
    }

    /**
     * MenuItemモデル → MenuItemDTO に再帰的に変換
     *
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Support\Collection>  $childrenByParent  parent_idでグルーピングされた子アイテム
     */
    private function buildItemDTO(mixed $item, \Illuminate\Support\Collection $childrenByParent): MenuItemDTO
    {
        $children = [];
        if ($childrenByParent->has($item->id)) {
            $children = $childrenByParent->get($item->id)
                ->map(fn ($child) => $this->buildItemDTO($child, $childrenByParent))
                ->values()
                ->toArray();
        }

        return new MenuItemDTO(
            label: $item->title,
            url: $item->url ?? '#',
            target: $item->target ?? '_self',
            sourceType: $item->source_type,
            sourceId: $item->source_id,
            iconClass: $item->icon_class,
            cssClass: $item->css_class,
            displayOrder: $item->display_order ?? 0,
            isActive: true,
            children: $children,
        );
    }
}
