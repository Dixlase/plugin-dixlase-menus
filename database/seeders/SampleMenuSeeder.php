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

namespace Plugins\DixlaseMenus\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Default sample menu seeder.
 *
 * Creates a main menu and a footer menu
 * so that the plugin is immediately usable after installation.
 */
class SampleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lang = app()->getLocale();
        $isJapanese = $lang === 'ja';

        // Skip if sample menus already exist for this language
        if (DB::table('plg_dixlase_menus')->where('slug', 'main-menu')->where('lang', $lang)->exists()) {
            return;
        }

        // Main menu
        $menuId = DB::table('plg_dixlase_menus')->insertGetId([
            'name' => $isJapanese ? 'メインメニュー' : 'Main Menu',
            'slug' => 'main-menu',
            'lang' => $lang,
            'location' => 'header',
            'placement_type' => 'manual',
            'description' => $isJapanese ? 'サイトのメインナビゲーションメニュー' : 'Main navigation menu for the site',
            'is_active' => true,
            'display_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Home
        DB::table('plg_dixlase_menu_items')->insert([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => $isJapanese ? 'ホーム' : 'Home',
            'url' => '/',
            'source_type' => 'custom_url',
            'source_id' => null,
            'target' => '_self',
            'css_class' => null,
            'icon_class' => 'fas fa-home',
            'description' => null,
            'depth' => 0,
            'display_order' => 1,
            'is_active' => true,
            'is_visible' => true,
            'visibility_conditions' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // About Us
        DB::table('plg_dixlase_menu_items')->insert([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => $isJapanese ? '私たちについて' : 'About Us',
            'url' => '/about',
            'source_type' => 'custom_url',
            'source_id' => null,
            'target' => '_self',
            'css_class' => null,
            'icon_class' => null,
            'description' => null,
            'depth' => 0,
            'display_order' => 2,
            'is_active' => true,
            'is_visible' => true,
            'visibility_conditions' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Footer menu (empty — items added by user via Legal/SEO plugins)
        DB::table('plg_dixlase_menus')->insert([
            'name' => $isJapanese ? 'フッターメニュー' : 'Footer Menu',
            'slug' => 'footer-menu',
            'lang' => $lang,
            'location' => 'footer',
            'placement_type' => 'manual',
            'description' => $isJapanese ? 'フッターに表示されるメニュー' : 'Navigation menu displayed in the footer',
            'is_active' => true,
            'display_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
