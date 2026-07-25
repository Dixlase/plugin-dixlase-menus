<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc. and Dixlase contributors
 * https://exc-d.com
 *
 * Dixlase Menus is dual-licensed. You may use this file under either:
 *
 *   (a) the GNU General Public License version 3 or later, as published
 *       by the Free Software Foundation; or
 *
 *   (b) a commercial license agreement obtained from exc-D inc.
 *
 * Unless you have entered into a commercial license agreement, this
 * file is governed by the GPL terms below.
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
 * Default menu settings seeder.
 */
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
                'description' => 'Maximum menu nesting depth',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_menu_cache',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable menu cache',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cache_duration',
                'value' => '3600',
                'type' => 'integer',
                'description' => 'Cache duration in seconds',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_target',
                'value' => '_self',
                'type' => 'string',
                'description' => 'Default link target',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'available_locations',
                'value' => json_encode(['header', 'footer', 'sidebar', 'mobile']),
                'type' => 'json',
                'description' => 'Available menu locations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('plg_dixlase_menu_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
