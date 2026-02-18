<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーション実行
     */
    public function up(): void
    {
        Schema::table('plg_dixlase_menus', function (Blueprint $table) {
            $table->string('placement_type', 20)->default('manual')->after('location');
        });
    }

    /**
     * マイグレーションロールバック
     */
    public function down(): void
    {
        Schema::table('plg_dixlase_menus', function (Blueprint $table) {
            $table->dropColumn('placement_type');
        });
    }
};
