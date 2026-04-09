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

namespace Plugins\DixlaseMenus\App\Repositories;

use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenus\App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * メニューリポジトリ実装
 */
class MenuRepository implements MenuRepositoryInterface
{
    /**
     * キャッシュキーのプレフィックス
     */
    protected const CACHE_PREFIX = 'menu:';

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return Menu::ordered()->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getActive(): Collection
    {
        return Menu::active()->ordered()->get();
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Menu
    {
        return Menu::find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail(int $id): Menu
    {
        return Menu::findOrFail($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Menu
    {
        return Menu::bySlug($slug)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function getByLocation(string $location): Collection
    {
        return Menu::byLocation($location)->ordered()->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveByLocation(string $location): ?Menu
    {
        return Menu::active()
            ->byLocation($location)
            ->ordered()
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Menu
    {
        return Menu::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): Menu
    {
        $menu = $this->findOrFail($id);
        $menu->update($data);

        return $menu->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $menu = $this->findOrFail($id);

        return $menu->forceDelete();
    }

    /**
     * {@inheritDoc}
     */
    public function softDelete(int $id): bool
    {
        $menu = $this->findOrFail($id);

        return $menu->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function restore(int $id): bool
    {
        $menu = Menu::withTrashed()->findOrFail($id);

        return $menu->restore();
    }

    /**
     * {@inheritDoc}
     */
    public function findWithItems(int $id): ?Menu
    {
        return Menu::with('items')->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findWithActiveItems(int $id): ?Menu
    {
        return Menu::with('activeItems')->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findWithHierarchy(int $id, int $depth = 3): ?Menu
    {
        $with = $this->buildHierarchyRelations($depth);
        
        return Menu::with($with)->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getCached(string $slug, int $ttl = 3600): ?Menu
    {
        $cacheKey = self::CACHE_PREFIX . $slug;
        
        return Cache::remember($cacheKey, $ttl, function () use ($slug) {
            return Menu::bySlug($slug)
                ->with('rootItems.activeChildren')
                ->first();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function clearCache(string $slug): void
    {
        Cache::forget(self::CACHE_PREFIX . $slug);
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'all');
    }

    /**
     * {@inheritDoc}
     */
    public function exists(int $id): bool
    {
        return Menu::where('id', $id)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Menu::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * 階層構造のリレーション文字列を構築
     *
     * @param int $depth
     * @return array
     */
    protected function buildHierarchyRelations(int $depth): array
    {
        $relations = ['rootItems'];
        
        for ($i = 1; $i < $depth; $i++) {
            $relations[] = str_repeat('children.', $i) . 'children';
        }
        
        return $relations;
    }

    /**
     * デフォルトメニューを取得（存在しない場合は作成）
     *
     * @return Menu
     */
    public function getOrCreateDefault(): Menu
    {
        $defaultSlug = 'main-menu';
        
        $menu = $this->findBySlug($defaultSlug);
        
        if (!$menu) {
            $menu = $this->create([
                'name' => 'Main Menu',
                'slug' => $defaultSlug,
                'location' => 'header',
                'description' => 'Default main navigation menu',
                'is_active' => true,
                'display_order' => 0,
            ]);
        }
        
        return $menu;
    }

    /**
     * 最初のメニューを取得（存在しない場合はデフォルトを作成）
     *
     * @return Menu
     */
    public function getFirstOrCreateDefault(): Menu
    {
        $menu = Menu::ordered()->first();
        
        if (!$menu) {
            return $this->getOrCreateDefault();
        }
        
        return $menu;
    }
}
