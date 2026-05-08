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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add title_translations JSON column to menu_items and backfill from
     * the parent menu's lang code so existing items keep their current label.
     */
    public function up(): void
    {
        Schema::table('plg_dixlase_menu_items', function (Blueprint $table) {
            $table->json('title_translations')->nullable()->after('title');
        });

        // Backfill: copy current title into title_translations[$menu->lang]
        $rows = DB::table('plg_dixlase_menu_items as mi')
            ->join('plg_dixlase_menus as m', 'm.id', '=', 'mi.menu_id')
            ->select('mi.id', 'mi.title', 'm.lang')
            ->whereNull('mi.title_translations')
            ->get();

        foreach ($rows as $row) {
            $lang = $row->lang ?: 'en';
            DB::table('plg_dixlase_menu_items')
                ->where('id', $row->id)
                ->update([
                    'title_translations' => json_encode(
                        [$lang => $row->title],
                        JSON_UNESCAPED_UNICODE
                    ),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('plg_dixlase_menu_items', function (Blueprint $table) {
            $table->dropColumn('title_translations');
        });
    }
};
