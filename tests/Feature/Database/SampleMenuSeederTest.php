<?php

/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
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

namespace Plugins\DixlaseMenus\Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Plugins\DixlaseMenus\Database\Seeders\DatabaseSeeder;
use Plugins\DixlaseMenus\Database\Seeders\MenuSettingsSeeder;
use Plugins\DixlaseMenus\Database\Seeders\SampleMenuSeeder;
use Tests\TestCase;

/**
 * Tests for plugin database seeders.
 */
class SampleMenuSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => base_path('plugins/DixlaseMenus/database/migrations'),
            '--realpath' => true,
        ]);
    }

    public function test_sample_menu_seeder_creates_header_and_footer_menus(): void
    {
        $this->seed(SampleMenuSeeder::class);

        $lang = app()->getLocale();

        $this->assertDatabaseHas('plg_dixlase_menus', [
            'slug' => 'main-menu',
            'lang' => $lang,
            'location' => 'header',
            'placement_type' => 'manual',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('plg_dixlase_menus', [
            'slug' => 'footer-menu',
            'lang' => $lang,
            'location' => 'footer',
            'placement_type' => 'manual',
            'is_active' => true,
        ]);
    }

    public function test_sample_menu_seeder_creates_menu_items(): void
    {
        $this->seed(SampleMenuSeeder::class);

        $mainMenu = DB::table('plg_dixlase_menus')->where('slug', 'main-menu')->first();
        $footerMenu = DB::table('plg_dixlase_menus')->where('slug', 'footer-menu')->first();

        // Main menu: Home, About Us = 2 items
        $mainItemCount = DB::table('plg_dixlase_menu_items')
            ->where('menu_id', $mainMenu->id)
            ->count();
        $this->assertSame(2, $mainItemCount);

        // Footer menu: empty (items added by user via plugins)
        $footerItemCount = DB::table('plg_dixlase_menu_items')
            ->where('menu_id', $footerMenu->id)
            ->count();
        $this->assertSame(0, $footerItemCount);
    }

    public function test_sample_menu_seeder_creates_flat_items(): void
    {
        $this->seed(SampleMenuSeeder::class);

        $mainMenu = DB::table('plg_dixlase_menus')->where('slug', 'main-menu')->first();

        // All items should be at root level (depth 0)
        $childItems = DB::table('plg_dixlase_menu_items')
            ->where('menu_id', $mainMenu->id)
            ->where('depth', '>', 0)
            ->count();
        $this->assertSame(0, $childItems);
    }

    public function test_sample_menu_seeder_skips_duplicate_run(): void
    {
        $this->seed(SampleMenuSeeder::class);
        $this->seed(SampleMenuSeeder::class);

        $menuCount = DB::table('plg_dixlase_menus')->where('slug', 'main-menu')->count();
        $this->assertSame(1, $menuCount);
    }

    public function test_menu_settings_seeder_creates_default_settings(): void
    {
        $this->seed(MenuSettingsSeeder::class);

        $this->assertDatabaseHas('plg_dixlase_menu_settings', ['key' => 'max_menu_depth', 'value' => '2']);
        $this->assertDatabaseHas('plg_dixlase_menu_settings', ['key' => 'enable_menu_cache', 'value' => '1']);
        $this->assertDatabaseHas('plg_dixlase_menu_settings', ['key' => 'cache_duration', 'value' => '3600']);
        $this->assertDatabaseHas('plg_dixlase_menu_settings', ['key' => 'default_target', 'value' => '_self']);
        $this->assertDatabaseHas('plg_dixlase_menu_settings', ['key' => 'available_locations']);
    }

    public function test_menu_settings_seeder_is_idempotent(): void
    {
        $this->seed(MenuSettingsSeeder::class);
        $this->seed(MenuSettingsSeeder::class);

        $count = DB::table('plg_dixlase_menu_settings')->where('key', 'max_menu_depth')->count();
        $this->assertSame(1, $count);
    }

    public function test_database_seeder_runs_both_seeders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('plg_dixlase_menus', ['slug' => 'main-menu']);
        $this->assertDatabaseHas('plg_dixlase_menu_settings', ['key' => 'max_menu_depth']);
    }
}
