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
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plg_dixlase_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('メニュー名');
            $table->string('slug')->comment('メニュースラッグ（識別子）');
            $table->string('lang', 10)->comment('言語コード');
            $table->string('location')->nullable()->comment('表示位置（header, footer, sidebarなど）');
            $table->text('description')->nullable()->comment('メニューの説明');
            $table->boolean('is_active')->default(true)->comment('有効/無効');
            $table->integer('display_order')->default(0)->comment('表示順');
            $table->timestamps();
            $table->softDeletes();

            // ソフトデリート対応のユニーク制約（slug + lang + deleted_at）
            $table->unique(['slug', 'lang', 'deleted_at'], 'plg_dixlase_menus_slug_lang_del_unique');

            // インデックス
            $table->index('slug');
            $table->index('lang');
            $table->index('location');
            $table->index('is_active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plg_dixlase_menus');
    }
};
