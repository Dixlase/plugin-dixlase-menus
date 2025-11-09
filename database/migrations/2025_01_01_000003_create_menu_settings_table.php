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
        Schema::create('menu_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('設定キー');
            $table->text('value')->nullable()->comment('設定値（JSON形式も可）');
            $table->string('type')->default('string')->comment('値の型（string, boolean, integer, json）');
            $table->text('description')->nullable()->comment('設定の説明');
            $table->timestamps();

            // インデックス
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_settings');
    }
};
