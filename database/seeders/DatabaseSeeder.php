<?php

namespace Plugins\DixlaseMenu\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // メニュー設定のシーダーを実行
        $this->call([
            MenuSettingsSeeder::class,
        ]);

        // 開発環境でのみサンプルメニューを作成
        if (!app()->environment('production')) {
            $this->call([
                SampleMenuSeeder::class,
            ]);
        }
    }
}