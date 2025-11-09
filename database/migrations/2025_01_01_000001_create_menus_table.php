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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('メニュー名');
            $table->string('slug')->unique()->comment('メニュースラッグ（識別子）');
            $table->string('location')->nullable()->comment('表示位置（header, footer, sidebarなど）');
            $table->text('description')->nullable()->comment('メニューの説明');
            $table->boolean('is_active')->default(true)->comment('有効/無効');
            $table->integer('display_order')->default(0)->comment('表示順');
            $table->timestamps();
            $table->softDeletes();

            // インデックス
            $table->index('slug');
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
        Schema::dropIfExists('menus');
    }
};
