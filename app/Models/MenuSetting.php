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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * メニュー設定モデル
 * 
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MenuSetting extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'plg_dixlase_menu_settings';

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * デフォルト値
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => 'string',
    ];

    /**
     * 設定値を取得（型変換あり）
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * 設定値を保存
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @return MenuSetting
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null): MenuSetting
    {
        $stringValue = static::valueToString($value, $type);

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * 設定が存在するかチェック
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * 設定を削除
     *
     * @param string $key
     * @return bool
     */
    public static function remove(string $key): bool
    {
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * すべての設定を配列で取得
     *
     * @return array<string, mixed>
     */
    public static function getAllSettings(): array
    {
        return static::query()
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getTypedValue()];
            })
            ->toArray();
    }

    /**
     * 複数の設定を一括取得
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public static function getMultiple(array $keys): array
    {
        return static::whereIn('key', $keys)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => static::castValue($setting->value, $setting->type)];
            })
            ->toArray();
    }

    /**
     * 値を文字列に変換
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected static function valueToString($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * 文字列を適切な型に変換
     *
     * @param string|null $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue(?string $value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * 型でフィルタリングするスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * キーで検索するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * 型変換された値を取得するアクセサ
     *
     * @return mixed
     */
    public function getTypedValueAttribute()
    {
        return static::castValue($this->value, $this->type);
    }

    /**
     * boolean型かどうかを判定
     *
     * @return bool
     */
    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }

    /**
     * integer型かどうかを判定
     *
     * @return bool
     */
    public function isInteger(): bool
    {
        return $this->type === 'integer';
    }

    /**
     * JSON型かどうかを判定
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->type === 'json';
    }

    /**
     * string型かどうかを判定
     *
     * @return bool
     */
    public function isString(): bool
    {
        return $this->type === 'string';
    }
}
