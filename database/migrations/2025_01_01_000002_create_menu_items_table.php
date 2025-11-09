<?php

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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade')->comment('所属メニューID');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade')->comment('親アイテムID（階層構造用）');
            
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
        Schema::dropIfExists('menu_items');
    }
};
