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
        Schema::create('plg_dixlase_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('plg_dixlase_menus')->onDelete('cascade')->comment('所属メニューID');
            $table->foreignId('parent_id')->nullable()->constrained('plg_dixlase_menu_items')->onDelete('cascade')->comment('親アイテムID（階層構造用）');
            
            // リンク情報
            $table->string('title')->comment('表示テキスト');
            $table->string('url')->nullable()->comment('リンクURL');
            $table->string('source_type')->nullable()->comment('リンクソースタイプ（custom_url, page, categoryなど）');
            $table->string('source_id')->nullable()->comment('ソースアイテムID');
            
            // 表示設定
            $table->string('target')->default('_self')->comment('リンクターゲット（_self, _blank）');
            $table->string('css_class')->nullable()->comment('CSSクラス');
            $table->string('icon_class')->nullable()->comment('アイコンクラス（Font Awesomeなど）');
            $table->text('description')->nullable()->comment('アイテムの説明');
            
            // 階層・順序
            $table->integer('depth')->default(0)->comment('階層の深さ（0=ルート）');
            $table->integer('display_order')->default(0)->comment('表示順');
            
            // 状態
            $table->boolean('is_active')->default(true)->comment('有効/無効');
            $table->boolean('is_visible')->default(true)->comment('表示/非表示');
            
            // 権限・条件
            $table->json('visibility_conditions')->nullable()->comment('表示条件（ログイン状態、権限など）');
            
            $table->timestamps();
            $table->softDeletes();

            // インデックス
            $table->index('menu_id');
            $table->index('parent_id');
            $table->index(['source_type', 'source_id']);
            $table->index('is_active');
            $table->index('is_visible');
            $table->index(['menu_id', 'display_order']);
            $table->index(['menu_id', 'parent_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plg_dixlase_menu_items');
    }
};
