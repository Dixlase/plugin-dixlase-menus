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

use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuSettingRepositoryInterface;
use Plugins\DixlaseMenus\App\Models\MenuSetting;
use Illuminate\Support\Facades\Cache;

/**
 * メニュー設定リポジトリ実装
 */
class MenuSettingRepository implements MenuSettingRepositoryInterface
{
    /**
     * キャッシュキーのプレフィックス
     */
    protected const CACHE_PREFIX = 'menu_setting:';

    /**
     * {@inheritDoc}
     */
    public function get(string $key, $default = null)
    {
        return MenuSetting::get($key, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, $value, string $type = 'string', ?string $description = null): MenuSetting
    {
        $setting = MenuSetting::set($key, $value, $type, $description);
        $this->clearCache($key);
        
        return $setting;
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple(array $keys): array
    {
        return MenuSetting::getMultiple($keys);
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple(array $settings): bool
    {
        try {
            foreach ($settings as $setting) {
                $this->set(
                    $setting['key'],
                    $setting['value'],
                    $setting['type'] ?? 'string',
                    $setting['description'] ?? null
                );
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return MenuSetting::all();
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return MenuSetting::has($key);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key): bool
    {
        $result = MenuSetting::remove($key);
        
        if ($result) {
            $this->clearCache($key);
        }
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getByType(string $type): array
    {
        return MenuSetting::byType($type)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->typed_value];
            })
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getBoolean(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * {@inheritDoc}
     */
    public function getInteger(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return (int) $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getJson(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);
        return is_array($value) ? $value : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getCached(string $key, $default = null, int $ttl = 3600)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        
        return Cache::remember($cacheKey, $ttl, function () use ($key, $default) {
            return $this->get($key, $default);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        } else {
            // すべての設定キャッシュをクリア
            $keys = MenuSetting::pluck('key');
            foreach ($keys as $k) {
                Cache::forget(self::CACHE_PREFIX . $k);
            }
        }
    }
}
