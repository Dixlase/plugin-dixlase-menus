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

namespace Plugins\DixlaseMenus\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Plugins\DixlaseMenus\App\Enums\PlacementType;

/**
 * メニューモデル
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $lang 言語コード
 * @property string|null $location
 * @property PlacementType $placement_type
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
        'lang',
        'location',
        'placement_type',
        'description',
        'is_active',
        'display_order',
    ];

    /**
     * キャストする属性
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'placement_type' => PlacementType::class,
        ];
    }

    /**
     * デフォルト値
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'display_order' => 0,
        'placement_type' => 'manual',
    ];

    /**
     * When a menu is force-deleted, drop its row in the central
     * plg_dixlase_multilingual_translations table. The morph table has
     * no FK / cascade (it cannot reference a polymorphic target) so the
     * cleanup is wired here. Soft-deletes are intentionally NOT cleaned
     * up: a soft-deleted menu can be restored, and its translations
     * should come back with it.
     */
    protected static function booted(): void
    {
        static::forceDeleted(function (self $menu) {
            if (! \Illuminate\Support\Facades\Schema::hasTable('plg_dixlase_multilingual_translations')) {
                return;
            }

            \Illuminate\Support\Facades\DB::table('plg_dixlase_multilingual_translations')
                ->where('translatable_type', 'dixlase-menus:menu')
                ->where('translatable_id', $menu->getKey())
                ->delete();
        });
    }

    /**
     * Dynamic translatable-field definitions consumed by the
     * DixlaseMultilingual translation editor (duck-typed; see that
     * plugin's DixlaseMultilingualAdminTranslationsController::resolveFields).
     *
     * A menu's translatable content is the label of each of its items,
     * so this returns one field per menu item -- name keyed by item id
     * (item_<id>), label/source set to the item's primary-locale title,
     * and depth carrying the tree level so the editor can render the
     * parent / child / grandchild hierarchy with indentation.
     *
     * The list reflects the menu's CURRENT items every time it is
     * called: items added after a translation was first written show up
     * with empty inputs, and items since deleted simply fall off (the
     * controller prunes their stale keys from the stored row on save).
     *
     * @return array<int, array<string, mixed>>
     */
    public function translatableFieldDefinitions(): array
    {
        $all = $this->items()->orderBy('display_order')->get();

        // Bucket items by parent so we can walk the tree without N+1
        // queries. parent_id null (root items) is bucketed under 0.
        $childrenOf = [];
        foreach ($all as $item) {
            $childrenOf[$item->parent_id ?? 0][] = $item;
        }

        $definitions = [];
        $this->appendItemFieldDefinitions($childrenOf, 0, 0, $definitions);

        return $definitions;
    }

    /**
     * Depth-first walk that appends one field definition per menu item.
     *
     * @param  array<int, array<int, MenuItem>>  $childrenOf
     * @param  array<int, array<string, mixed>>  $definitions
     */
    private function appendItemFieldDefinitions(
        array $childrenOf,
        int $parentKey,
        int $depth,
        array &$definitions
    ): void {
        foreach ($childrenOf[$parentKey] ?? [] as $item) {
            $primaryTitle = (string) $item->getRawOriginal('title');

            $definitions[] = [
                'name' => 'item_'.$item->id,
                'label' => $primaryTitle,
                'source' => $primaryTitle,
                'type' => 'text',
                'rules' => 'nullable|string|max:255',
                'depth' => $depth,
            ];

            $this->appendItemFieldDefinitions($childrenOf, $item->id, $depth + 1, $definitions);
        }
    }

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
     * 指定言語のメニューを取得するスコープ
     */
    public function scopeForLang(Builder $query, string $lang): Builder
    {
        return $query->where('lang', $lang);
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
