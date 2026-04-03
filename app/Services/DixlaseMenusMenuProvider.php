<?php

/**
 * This file is part of Dixlase.
 *
 * Copyright (C) 2026 exc-D inc.
 * https://exc-d.com
 *
 * Licensed under the GPL-3.0 License.
 * See LICENSE file in the plugin root for details.
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
