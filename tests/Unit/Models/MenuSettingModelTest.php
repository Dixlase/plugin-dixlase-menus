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

namespace Plugins\DixlaseMenus\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\DixlaseMenus\App\Models\MenuSetting;
use Tests\TestCase;

class MenuSettingModelTest extends TestCase
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

    public function test_set_and_get(): void
    {
        MenuSetting::set('test_key', 'test_value');

        $this->assertEquals('test_value', MenuSetting::get('test_key'));
    }

    public function test_get_returns_default_for_missing(): void
    {
        $this->assertEquals('fallback', MenuSetting::get('nonexistent', 'fallback'));
    }

    public function test_has(): void
    {
        $this->assertFalse(MenuSetting::has('new_key'));

        MenuSetting::set('new_key', 'value');

        $this->assertTrue(MenuSetting::has('new_key'));
    }

    public function test_remove(): void
    {
        MenuSetting::set('temp_key', 'temp');

        $this->assertTrue(MenuSetting::remove('temp_key'));
        $this->assertFalse(MenuSetting::has('temp_key'));
    }

    public function test_remove_nonexistent_returns_false(): void
    {
        $this->assertFalse(MenuSetting::remove('nonexistent'));
    }

    public function test_set_with_boolean_type(): void
    {
        MenuSetting::set('flag', true, 'boolean');

        $value = MenuSetting::get('flag');
        $this->assertTrue($value);
    }

    public function test_set_with_integer_type(): void
    {
        MenuSetting::set('count', 42, 'integer');

        $this->assertEquals(42, MenuSetting::get('count'));
    }

    public function test_overwrite_existing(): void
    {
        MenuSetting::set('key', 'original');
        MenuSetting::set('key', 'updated');

        $this->assertEquals('updated', MenuSetting::get('key'));
    }

    public function test_multiple_settings_can_be_stored(): void
    {
        MenuSetting::set('a', '1');
        MenuSetting::set('b', '2');

        $this->assertEquals('1', MenuSetting::get('a'));
        $this->assertEquals('2', MenuSetting::get('b'));
    }

    public function test_get_multiple(): void
    {
        MenuSetting::set('x', 'val_x');
        MenuSetting::set('y', 'val_y');

        $result = MenuSetting::getMultiple(['x', 'y', 'z']);

        $this->assertEquals('val_x', $result['x']);
        $this->assertEquals('val_y', $result['y']);
        $this->assertNull($result['z'] ?? null);
    }
}
