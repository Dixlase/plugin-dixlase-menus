<?php

/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
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

namespace Plugins\DixlaseMenu\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * メニューモデル
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $location
 * @property string|null $description
 * @property bool $is_active
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Menu extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'plg_dixlase_menus';

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_active',
        'display_order',
    ];

    /**
     * キャストする属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * デフォルト値
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'display_order' => 0,
    ];

    /**
     * メニューアイテムとのリレーション
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->orderBy('display_order');
    }

    /**
     * ルートレベルのメニューアイテムのみ取得
     *
     * @return HasMany
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->orderBy('display_order');
    }

    /**
     * 有効なメニューアイテムのみ取得
     *
     * @return HasMany
     */
    public function activeItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('display_order');
    }

    /**
     * 有効なメニューのみ取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 特定の位置のメニューを取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $location
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * スラッグでメニューを取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * 表示順でソートするスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query, string $direction = 'asc')
    {
        return $query->orderBy('display_order', $direction);
    }

    /**
     * メニューアイテムの総数を取得
     *
     * @return int
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * 有効なメニューアイテムの数を取得
     *
     * @return int
     */
    public function getActiveItemsCountAttribute(): int
    {
        return $this->activeItems()->count();
    }

    /**
     * メニューが空かどうかを判定
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }

    /**
     * メニューに階層構造があるかどうかを判定
     *
     * @return bool
     */
    public function hasHierarchy(): bool
    {
        return $this->items()->where('depth', '>', 0)->exists();
    }
}
