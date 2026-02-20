<?php

namespace Plugins\DixlaseMenus\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // サンプルメニューを作成
        $menuId = DB::table('menus')->insertGetId([
            'name' => 'メインメニュー',
            'slug' => 'main-menu',
            'location' => 'header',
            'description' => 'ヘッダーに表示されるメインメニュー',
            'is_active' => true,
            'display_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // サンプルメニューアイテムを作成
        $homeItemId = DB::table('menu_items')->insertGetId([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => 'ホーム',
            'url' => '/',
            'source_type' => 'custom_url',
            'source_id' => null,
            'target' => '_self',
            'css_class' => null,
            'icon_class' => 'fas fa-home',
            'description' => 'トップページへのリンク',
            'depth' => 0,
            'display_order' => 1,
            'is_active' => true,
            'is_visible' => true,
            'visibility_conditions' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $aboutItemId = DB::table('menu_items')->insertGetId([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => '会社概要',
            'url' => '/about',
            'source_type' => 'custom_url',
            'source_id' => null,
            'target' => '_self',
            'css_class' => null,
            'icon_class' => 'fas fa-building',
            'description' => '会社概要ページへのリンク',
            'depth' => 0,
            'display_order' => 2,
            'is_active' => true,
            'is_visible' => true,
            'visibility_conditions' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // サブメニューアイテム（会社概要の下）
        DB::table('menu_items')->insert([
            [
                'menu_id' => $menuId,
                'parent_id' => $aboutItemId,
                'title' => '企業理念',
                'url' => '/about/philosophy',
                'source_type' => 'custom_url',
                'source_id' => null,
                'target' => '_self',
                'css_class' => null,
                'icon_class' => null,
                'description' => '企業理念ページへのリンク',
                'depth' => 1,
                'display_order' => 1,
                'is_active' => true,
                'is_visible' => true,
                'visibility_conditions' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'menu_id' => $menuId,
                'parent_id' => $aboutItemId,
                'title' => '会社沿革',
                'url' => '/about/history',
                'source_type' => 'custom_url',
                'source_id' => null,
                'target' => '_self',
                'css_class' => null,
                'icon_class' => null,
                'description' => '会社沿革ページへのリンク',
                'depth' => 1,
                'display_order' => 2,
                'is_active' => true,
                'is_visible' => true,
                'visibility_conditions' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('menu_items')->insert([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => 'お問い合わせ',
            'url' => '/contact',
            'source_type' => 'custom_url',
            'source_id' => null,
            'target' => '_self',
            'css_class' => null,
            'icon_class' => 'fas fa-envelope',
            'description' => 'お問い合わせページへのリンク',
            'depth' => 0,
            'display_order' => 3,
            'is_active' => true,
            'is_visible' => true,
            'visibility_conditions' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // フッターメニューも作成
        $footerMenuId = DB::table('menus')->insertGetId([
            'name' => 'フッターメニュー',
            'slug' => 'footer-menu',
            'location' => 'footer',
            'description' => 'フッターに表示されるメニュー',
            'is_active' => true,
            'display_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_items')->insert([
            [
                'menu_id' => $footerMenuId,
                'parent_id' => null,
                'title' => 'プライバシーポリシー',
                'url' => '/privacy',
                'source_type' => 'custom_url',
                'source_id' => null,
                'target' => '_self',
                'css_class' => null,
                'icon_class' => null,
                'description' => 'プライバシーポリシーページへのリンク',
                'depth' => 0,
                'display_order' => 1,
                'is_active' => true,
                'is_visible' => true,
                'visibility_conditions' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'menu_id' => $footerMenuId,
                'parent_id' => null,
                'title' => '利用規約',
                'url' => '/terms',
                'source_type' => 'custom_url',
                'source_id' => null,
                'target' => '_self',
                'css_class' => null,
                'icon_class' => null,
                'description' => '利用規約ページへのリンク',
                'depth' => 0,
                'display_order' => 2,
                'is_active' => true,
                'is_visible' => true,
                'visibility_conditions' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
