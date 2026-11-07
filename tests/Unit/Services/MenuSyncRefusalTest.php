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

namespace Plugins\DixlaseMenus\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuItemRepositoryInterface;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenus\App\Models\Menu;
use Plugins\DixlaseMenus\App\Models\MenuItem;
use Plugins\DixlaseMenus\App\Repositories\MenuItemRepository;
use Plugins\DixlaseMenus\App\Repositories\MenuRepository;
use Plugins\DixlaseMenus\App\Services\MenuService;
use Tests\TestCase;

/**
 * A refused link is an input error, and is reported as one.
 *
 * The editor saves every item in one request through syncMenuItems(),
 * which wrapped the whole sync in `catch (\Exception)`. ValidationException
 * is an \Exception, so a refused link landed in the branch meant for real
 * failures: logged at error level with a stack trace, answered with a 500,
 * and -- because the default log stack carries error to the
 * system-notification handler -- mailed to the administrator as a system
 * error. See #70, and #62 for the same shape on the item form.
 */
class MenuSyncRefusalTest extends TestCase
{
    use RefreshDatabase;

    private Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => base_path('plugins/DixlaseMenus/database/migrations'),
            '--realpath' => true,
        ]);

        $this->app['translator']->addNamespace(
            'dixlase-menus',
            base_path('plugins/DixlaseMenus/lang')
        );

        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);

        $this->menu = Menu::create([
            'name' => 'Test Menu',
            'slug' => 'test',
            'lang' => 'ja',
            'is_active' => true,
        ]);
    }

    private function service(): MenuService
    {
        return app(MenuService::class);
    }

    public function test_a_refused_link_is_raised_as_a_validation_error(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->syncMenuItems($this->menu->id, [
            ['label' => 'Bad item', 'url' => 'javascript:alert(1)', 'source_type' => 'custom_url'],
        ]);
    }

    public function test_a_refused_link_is_not_logged_as_an_error(): void
    {
        // The error level is what reaches the system-notification handler,
        // so this is the assertion that keeps a typo from mailing the
        // administrator.
        Log::shouldReceive('error')->never();

        try {
            $this->service()->syncMenuItems($this->menu->id, [
                ['label' => 'Bad item', 'url' => 'javascript:alert(1)', 'source_type' => 'custom_url'],
            ]);
            $this->fail('A refused link must raise a validation error.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('url', $e->errors());
        }
    }

    public function test_the_message_names_the_item(): void
    {
        try {
            $this->service()->syncMenuItems($this->menu->id, [
                ['label' => 'Good item', 'url' => '/about', 'source_type' => 'custom_url'],
                ['label' => 'Second item', 'url' => 'javascript:alert(1)', 'source_type' => 'custom_url'],
            ]);
            $this->fail('A refused link must raise a validation error.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString(
                'Second item',
                $e->errors()['url'][0],
                'The editor saves every item at once, so the message has to say which one was refused.'
            );
        }
    }

    public function test_nothing_is_saved_when_one_item_is_refused(): void
    {
        // The sync runs in a transaction: the good item above the bad one
        // must not survive on its own.
        try {
            $this->service()->syncMenuItems($this->menu->id, [
                ['label' => 'Good item', 'url' => '/about', 'source_type' => 'custom_url'],
                ['label' => 'Bad item', 'url' => 'javascript:alert(1)', 'source_type' => 'custom_url'],
            ]);
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(0, MenuItem::where('menu_id', $this->menu->id)->count());
    }

    public function test_an_ordinary_save_still_works(): void
    {
        $result = $this->service()->syncMenuItems($this->menu->id, [
            ['label' => 'About', 'url' => '/about', 'source_type' => 'custom_url'],
            ['label' => 'Mail', 'url' => 'mailto:someone@example.com', 'source_type' => 'custom_url'],
        ]);

        $this->assertTrue($result);
        $this->assertSame(2, MenuItem::where('menu_id', $this->menu->id)->count());
    }

    public function test_the_refusal_is_caught_before_the_branch_that_logs(): void
    {
        $source = (string) file_get_contents(base_path('plugins/DixlaseMenus/app/Services/MenuService.php'));

        $validationAt = strpos($source, 'catch (ValidationException $e)');
        $genericAt = strpos($source, 'catch (\\Exception $e)');

        $this->assertNotFalse($validationAt);
        $this->assertNotFalse($genericAt);
        $this->assertLessThan(
            $genericAt,
            $validationAt,
            'PHP takes the first matching catch, so the validation branch has to come first.'
        );
    }

    public function test_the_controller_answers_with_the_validation_error(): void
    {
        $source = (string) file_get_contents(
            base_path('plugins/DixlaseMenus/app/Http/Controllers/Admin/AdminMenuItemController.php')
        );

        $this->assertMatchesRegularExpression(
            '/catch \(ValidationException \$e\).*?\$e->errors\(\).*?\], 422\)/s',
            $source,
            'A refused link is answered with 422 and the messages, not a generic 500.'
        );
    }

    public function test_the_editor_releases_the_confirmation_modal(): void
    {
        $js = (string) file_get_contents(
            base_path('plugins/DixlaseMenus/resources/src/admin/js/menu-editor.js')
        );

        // The modal set its submitting flag when it asked for this save and
        // only clears it when the submit call returns false, which an async
        // save never does. Without this the modal spins and its own close
        // paths stay disabled.
        $this->assertStringContainsString("window.closeModal('confirmationModal')", $js);

        // Both failure paths go through the one place that does it.
        $this->assertSame(2, substr_count($js, 'this.failSave('));
    }
}
