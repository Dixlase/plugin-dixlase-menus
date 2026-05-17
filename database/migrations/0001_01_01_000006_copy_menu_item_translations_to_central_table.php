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
    private const TYPE_KEY = 'dixlase-menus:menu-item';
    private const CENTRAL_TABLE = 'plg_dixlase_multilingual_translations';
    private const ITEMS_TABLE = 'plg_dixlase_menu_items';

    /**
     * Copy each menu_items.title_translations[locale]=value into the
     * central plg_dixlase_multilingual_translations table as one row per
     * (item, locale) with fields={"title": value}. UPSERTs on the unique
     * (translatable_type, translatable_id, locale) key so re-running is
     * a no-op when the row already matches and a safe overwrite when it
     * has drifted.
     *
     * The title_translations JSON column is intentionally left in place
     * for one release as a deprecated rollback path; a follow-up
     * migration drops it once the central path is verified in production.
     */
    public function up(): void
    {
        if (! Schema::hasTable(self::CENTRAL_TABLE)) {
            // DixlaseMultilingual is not installed/migrated yet. Nothing to
            // do; the central table will exist once it is, and operators
            // can re-run plugin migrations to backfill.
            return;
        }

        if (! Schema::hasTable(self::ITEMS_TABLE)) {
            return;
        }

        if (! Schema::hasColumn(self::ITEMS_TABLE, 'title_translations')) {
            return;
        }

        $rows = DB::table(self::ITEMS_TABLE)
            ->select('id', 'title_translations')
            ->whereNotNull('title_translations')
            ->get();

        $now = now();
        $copied = 0;

        foreach ($rows as $row) {
            $decoded = json_decode((string) $row->title_translations, true);
            if (! is_array($decoded) || empty($decoded)) {
                continue;
            }

            foreach ($decoded as $locale => $value) {
                if (! is_string($locale) || $locale === '') {
                    continue;
                }
                if (! is_string($value) || $value === '') {
                    continue;
                }

                DB::table(self::CENTRAL_TABLE)->upsert(
                    [
                        [
                            'translatable_type' => self::TYPE_KEY,
                            'translatable_id' => $row->id,
                            'locale' => $locale,
                            'fields' => json_encode(
                                ['title' => $value],
                                JSON_UNESCAPED_UNICODE
                            ),
                            'is_published' => 1,
                            'published_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ],
                    ['translatable_type', 'translatable_id', 'locale'],
                    ['fields', 'is_published', 'published_at', 'updated_at']
                );
                $copied++;
            }
        }

        // The copy itself is idempotent; logging keeps a breadcrumb in
        // deploy output so operators can verify the row count post-deploy.
        if ($copied > 0) {
            \Illuminate\Support\Facades\Log::info(
                'DixlaseMenus migration 000006: copied '.$copied.' translation rows to '.self::CENTRAL_TABLE
            );
        }
    }

    /**
     * Rollback removes the copied rows. It does NOT restore the JSON
     * column contents (they are still present in the items table during
     * the deprecation window, so rollback is a true inverse of up()).
     */
    public function down(): void
    {
        if (! Schema::hasTable(self::CENTRAL_TABLE)) {
            return;
        }

        DB::table(self::CENTRAL_TABLE)
            ->where('translatable_type', self::TYPE_KEY)
            ->delete();
    }
};
