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

namespace Plugins\DixlaseMenus\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * メニューアイテムモデル
 *
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $title
 * @property string|null $url
 * @property string|null $source_type
 * @property string|null $source_id
 * @property string $target
 * @property string|null $css_class
 * @property string|null $icon_class
 * @property string|null $description
 * @property int $depth
 * @property int $display_order
 * @property bool $is_active
 * @property bool $is_visible
 * @property array|null $visibility_conditions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'plg_dixlase_menu_items';

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'title_translations',
        'url',
        'source_type',
        'source_id',
        'target',
        'css_class',
        'icon_class',
        'description',
        'depth',
        'display_order',
        'is_active',
        'is_visible',
        'visibility_conditions',
    ];

    /**
     * キャストする属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'menu_id' => 'integer',
        'parent_id' => 'integer',
        'depth' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'visibility_conditions' => 'array',
        'title_translations' => 'array',
    ];

    /**
     * Get the title resolved for the given locale.
     *
     * Falls back to the primary `title` column when the locale has no
     * translation. The `title` column always holds the value entered when
     * the item was created (typically the primary locale's text).
     */
    public function getLocalizedTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translations = $this->title_translations ?? [];
        $value = $translations[$locale] ?? null;

        return is_string($value) && $value !== '' ? $value : (string) $this->title;
    }

    /**
     * デフォルト値
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'target' => '_self',
        'depth' => 0,
        'display_order' => 0,
        'is_active' => true,
        'is_visible' => true,
    ];

    /**
     * 所属するメニューとのリレーション
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * 親アイテムとのリレーション
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * 子アイテムとのリレーション
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('display_order');
    }

    /**
     * 有効な子アイテムのみ取得
     */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('display_order');
    }

    /**
     * すべての祖先アイテムを取得（再帰的）
     *
     * @return \Illuminate\Support\Collection
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * すべての子孫アイテムを取得（再帰的）
     *
     * @return \Illuminate\Support\Collection
     */
    public function descendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * 有効なアイテムのみ取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('is_visible', true);
    }

    /**
     * ルートレベルのアイテムのみ取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * 特定の深さのアイテムを取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepth($query, int $depth)
    {
        return $query->where('depth', $depth);
    }

    /**
     * 特定のソースタイプのアイテムを取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySourceType($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * 表示順でソートするスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query, string $direction = 'asc')
    {
        return $query->orderBy('display_order', $direction);
    }

    /**
     * ルートアイテムかどうかを判定
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * 子アイテムを持つかどうかを判定
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * 有効な子アイテムを持つかどうかを判定
     */
    public function hasActiveChildren(): bool
    {
        return $this->activeChildren()->exists();
    }

    /**
     * 外部リンクかどうかを判定
     */
    public function isExternalLink(): bool
    {
        if (! $this->url) {
            return false;
        }

        return str_starts_with($this->url, 'http://')
            || str_starts_with($this->url, 'https://');
    }

    /**
     * カスタムURLかどうかを判定
     */
    public function isCustomUrl(): bool
    {
        return $this->source_type === 'custom_url';
    }

    /**
     * アイコンを持つかどうかを判定
     */
    public function hasIcon(): bool
    {
        return ! empty($this->icon_class);
    }

    /**
     * 表示条件を満たすかどうかを判定
     */
    public function shouldDisplay(): bool
    {
        if (! $this->is_active || ! $this->is_visible) {
            return false;
        }

        // visibility_conditionsが設定されている場合は条件チェック
        if ($this->visibility_conditions) {
            // TODO: 条件チェックロジックを実装
            // 例: ログイン状態、権限、カスタム条件など
        }

        return true;
    }

    /**
     * 完全なパス（祖先のタイトル）を取得
     */
    public function getFullPath(string $separator = ' > '): string
    {
        $path = $this->ancestors()->pluck('title')->toArray();
        $path[] = $this->title;

        return implode($separator, $path);
    }

    /**
     * ラベル（表示用タイトル）を取得するアクセサ
     */
    public function getLabelAttribute(): string
    {
        return $this->title;
    }
}
