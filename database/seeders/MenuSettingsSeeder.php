<?php

namespace Plugins\DixlaseMenus\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'max_menu_depth',
                'value' => '3',
                'type' => 'integer',
                'description' => 'メニューの最大階層数',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_menu_cache',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'メニューのキャッシュを有効化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cache_duration',
                'value' => '3600',
                'type' => 'integer',
                'description' => 'キャッシュの有効期間（秒）',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_target',
                'value' => '_self',
                'type' => 'string',
                'description' => 'デフォルトのリンクターゲット',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'available_locations',
                'value' => json_encode(['header', 'footer', 'sidebar', 'mobile']),
                'type' => 'json',
                'description' => '利用可能なメニュー位置',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('menu_settings')->insert($settings);
    }
}
