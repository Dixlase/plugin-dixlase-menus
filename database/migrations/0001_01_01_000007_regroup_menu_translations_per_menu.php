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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CENTRAL_TABLE = 'plg_dixlase_multilingual_translations';
    private const ITEMS_TABLE = 'plg_dixlase_menu_items';

    private const OLD_TYPE = 'dixlase-menus:menu-item';
    private const NEW_TYPE = 'dixlase-menus:menu';

    /**
     * Re-group menu translations from per-item rows to per-menu rows.
     *
     * Migration 000006 copied translations as one central row per menu
     * item: (translatable_type='dixlase-menus:menu-item', id=<item id>,
     * locale, fields={"title": value}).
     *
     * The translatable entity is now the Menu, not the MenuItem: a menu
     * holds one row per locale whose fields map carries every item label
     * keyed by item_<id> -- e.g. fields={"item_5":"...", "item_6":"..."}.
     * This walks the old per-item rows, regroups them by the item's
     * parent menu, and writes the consolidated per-menu rows. The old
     * per-item rows are then deleted.
     *
     * Idempotent: an already-migrated database has no OLD_TYPE rows left,
     * so a re-run is a no-op.
     */
    public function up(): void
    {
        if (! Schema::hasTable(self::CENTRAL_TABLE) || ! Schema::hasTable(self::ITEMS_TABLE)) {
            return;
        }

        $oldRows = DB::table(self::CENTRAL_TABLE)
            ->where('translatable_type', self::OLD_TYPE)
            ->get();

        if ($oldRows->isEmpty()) {
            return;
        }

        // Map menu item id -> parent menu id so we know which menu row
        // each per-item translation belongs to.
        $menuIdByItemId = DB::table(self::ITEMS_TABLE)
            ->pluck('menu_id', 'id');

        // Accumulate per (menu_id, locale): the merged fields map plus
        // the most permissive publish state and earliest timestamps.
        $grouped = [];

        foreach ($oldRows as $row) {
            $menuId = $menuIdByItemId[$row->translatable_id] ?? null;
            if ($menuId === null) {
                // Orphan per-item row whose menu item no longer exists.
                // Nothing to attach it to; it is dropped with the rest
                // of the OLD_TYPE rows at the end of this migration.
                continue;
            }

            $itemFields = json_decode((string) $row->fields, true);
            $title = is_array($itemFields) ? ($itemFields['title'] ?? null) : null;
            if (! is_string($title) || $title === '') {
                continue;
            }

            $bucket = $menuId.':'.$row->locale;
            if (! isset($grouped[$bucket])) {
                $grouped[$bucket] = [
                    'menu_id' => $menuId,
                    'locale' => $row->locale,
                    'fields' => [],
                    'is_published' => (int) $row->is_published,
                    'published_at' => $row->published_at,
                    'created_at' => $row->created_at,
                ];
            }

            $grouped[$bucket]['fields']['item_'.$row->translatable_id] = $title;
            // A menu locale row counts as published if any contributing
            // item row was published.
            $grouped[$bucket]['is_published'] = max(
                $grouped[$bucket]['is_published'],
                (int) $row->is_published
            );
        }

        $now = now();

        foreach ($grouped as $entry) {
            DB::table(self::CENTRAL_TABLE)->upsert(
                [
                    [
                        'translatable_type' => self::NEW_TYPE,
                        'translatable_id' => $entry['menu_id'],
                        'locale' => $entry['locale'],
                        'fields' => json_encode($entry['fields'], JSON_UNESCAPED_UNICODE),
                        'is_published' => $entry['is_published'],
                        'published_at' => $entry['published_at'] ?? ($entry['is_published'] ? $now : null),
                        'created_at' => $entry['created_at'] ?? $now,
                        'updated_at' => $now,
                    ],
                ],
                ['translatable_type', 'translatable_id', 'locale'],
                ['fields', 'is_published', 'published_at', 'updated_at']
            );
        }

        // Remove the now-superseded per-item rows.
        DB::table(self::CENTRAL_TABLE)
            ->where('translatable_type', self::OLD_TYPE)
            ->delete();
    }

    /**
     * Rollback expands per-menu rows back into per-item rows. The menu
     * items table is the source of truth for item ids, so each item_<id>
     * key is unpacked into its own (dixlase-menus:menu-item) row.
     */
    public function down(): void
    {
        if (! Schema::hasTable(self::CENTRAL_TABLE)) {
            return;
        }

        $menuRows = DB::table(self::CENTRAL_TABLE)
            ->where('translatable_type', self::NEW_TYPE)
            ->get();

        $now = now();

        foreach ($menuRows as $row) {
            $fields = json_decode((string) $row->fields, true);
            if (! is_array($fields)) {
                continue;
            }

            foreach ($fields as $key => $value) {
                if (! is_string($key) || ! str_starts_with($key, 'item_')) {
                    continue;
                }
                $itemId = (int) substr($key, 5);
                if ($itemId <= 0 || ! is_string($value) || $value === '') {
                    continue;
                }

                DB::table(self::CENTRAL_TABLE)->upsert(
                    [
                        [
                            'translatable_type' => self::OLD_TYPE,
                            'translatable_id' => $itemId,
                            'locale' => $row->locale,
                            'fields' => json_encode(['title' => $value], JSON_UNESCAPED_UNICODE),
                            'is_published' => (int) $row->is_published,
                            'published_at' => $row->published_at ?? ($row->is_published ? $now : null),
                            'created_at' => $row->created_at ?? $now,
                            'updated_at' => $now,
                        ],
                    ],
                    ['translatable_type', 'translatable_id', 'locale'],
                    ['fields', 'is_published', 'published_at', 'updated_at']
                );
            }
        }

        DB::table(self::CENTRAL_TABLE)
            ->where('translatable_type', self::NEW_TYPE)
            ->delete();
    }
};
