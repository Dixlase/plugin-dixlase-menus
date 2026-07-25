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
            label: $this->resolveLabel($item),
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

    /**
     * Resolve the visible label for a menu item, gated on whether the
     * DixlaseMultilingual plugin's URL routing toggle is on.
     *
     * - Multilingual on  -> use getLocalizedTitle(), which reads
     *   app()->getLocale() (the multilingual plugin sets the runtime
     *   locale for /, /ja/, /en/ etc. via its middleware chain) and
     *   looks up title_translations[locale], falling back to title.
     * - Multilingual off (or plugin missing) -> return title verbatim
     *   so per-locale translations are not consulted at all.
     */
    private function resolveLabel(mixed $item): string
    {
        $title = (string) ($item->title ?? '');

        if (! $this->isMultilingualEnabled()) {
            return $title;
        }

        return method_exists($item, 'getLocalizedTitle')
            ? (string) $item->getLocalizedTitle()
            : $title;
    }

    /**
     * Runtime gate for multilingual menu rendering.
     *
     * Anchored on the capability declaration in plugin.json:
     *   "capabilities": ["multilingual-content"],
     *   "multilingual_content": { "types": [{ "key": "dixlase-menus:menu", ... }] }
     *
     * The DixlaseMultilingual plugin's TranslatableContentRegistry discovers
     * that declaration during boot and registers the menu type. If the
     * declaration is removed from plugin.json the registry no longer has the
     * type and this method returns false, so the menu silently stops
     * consulting translations. The locale_url_routing_enabled config check
     * additionally ensures the operator has actually turned multilingual on
     * (registry presence alone does not mean the feature is active).
     *
     * NB: this key must match plugin.json's multilingual_content type key.
     * It moved from dixlase-menus:menu-item to dixlase-menus:menu when the
     * translation model became per-menu; keeping the old value here would
     * make getType() miss and silently disable translated menu rendering.
     */
    private const MULTILINGUAL_CONTENT_TYPE_KEY = 'dixlase-menus:menu';

    private function isMultilingualEnabled(): bool
    {
        $registryClass = \Plugins\DixlaseMultilingual\App\Services\TranslatableContentRegistry::class;

        if (! class_exists($registryClass)) {
            return false;
        }

        if (! (bool) config('dixlase_multilingual.locale_url_routing_enabled', false)) {
            return false;
        }

        try {
            $registry = app($registryClass);
        } catch (\Throwable) {
            return false;
        }

        return $registry->getType(self::MULTILINGUAL_CONTENT_TYPE_KEY) !== null;
    }
}
