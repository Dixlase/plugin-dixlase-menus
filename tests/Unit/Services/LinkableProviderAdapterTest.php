<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc.
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

use App\Contracts\PluginIntegration\LinkableProviderInterface;
use App\DTO\PluginIntegration\LinkableDTO;
use Plugins\DixlaseMenus\App\Services\MenuLinkSources\LinkableProviderAdapter;
use Tests\TestCase;

/**
 * LinkableProviderAdapter ユニットテスト
 */
class LinkableProviderAdapterTest extends TestCase
{
    /**
     * getSourceType がプロバイダーの getProviderKey に委譲されること
     */
    public function test_get_source_type_delegates_to_provider_key(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getProviderKey')->willReturn('dixlase-pages');

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertSame('dixlase-pages', $adapter->getSourceType());
    }

    /**
     * getSourceLabel がプロバイダーの getProviderLabel に委譲されること
     */
    public function test_get_source_label_delegates_to_provider_label(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getProviderLabel')->willReturn('Pages');

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertSame('Pages', $adapter->getSourceLabel());
    }

    /**
     * getIcon がプロバイダーの getProviderIcon に委譲されること
     */
    public function test_get_icon_delegates_to_provider_icon(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getProviderIcon')->willReturn('fas fa-file-alt');

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertSame('fas fa-file-alt', $adapter->getIcon());
    }

    /**
     * getIcon がプロバイダーアイコン未設定時に null を返すこと
     */
    public function test_get_icon_returns_null_when_provider_has_no_icon(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getProviderIcon')->willReturn(null);

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertNull($adapter->getIcon());
    }

    /**
     * isAvailable がプロバイダーに委譲されること
     */
    public function test_is_available_delegates_to_provider(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('isAvailable')->willReturn(true);

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertTrue($adapter->isAvailable());
    }

    /**
     * getAvailableItems が DTO を配列形式に変換すること
     */
    public function test_get_available_items_converts_dtos_to_arrays(): void
    {
        $dto1 = new LinkableDTO(
            id: '1',
            title: 'Home',
            url: '/home',
            type: 'page',
            source: 'test',
        );
        $dto2 = new LinkableDTO(
            id: '2',
            title: 'About',
            url: '/about',
            type: 'page',
            source: 'test',
        );

        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getAvailableItems')->willReturn([$dto1, $dto2]);

        $adapter = new LinkableProviderAdapter($provider);
        $items = $adapter->getAvailableItems();

        $this->assertCount(2, $items);
        $this->assertSame(['id' => '1', 'title' => 'Home', 'url' => '/home'], $items[0]);
        $this->assertSame(['id' => '2', 'title' => 'About', 'url' => '/about'], $items[1]);
    }

    /**
     * searchItems が DTO を配列形式に変換すること
     */
    public function test_search_items_converts_dtos_to_arrays(): void
    {
        $dto = new LinkableDTO(
            id: '1',
            title: 'Search Result',
            url: '/result',
            type: 'page',
            source: 'test',
        );

        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('searchItems')
            ->with('query', 20)
            ->willReturn([$dto]);

        $adapter = new LinkableProviderAdapter($provider);
        $items = $adapter->searchItems('query');

        $this->assertCount(1, $items);
        $this->assertSame(['id' => '1', 'title' => 'Search Result', 'url' => '/result'], $items[0]);
    }

    /**
     * getItemById が存在するアイテムの配列を返すこと
     */
    public function test_get_item_by_id_returns_array_when_found(): void
    {
        $dto = new LinkableDTO(
            id: '5',
            title: 'Page Five',
            url: '/page-5',
            type: 'page',
            source: 'test',
        );

        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getItemById')->with('5')->willReturn($dto);

        $adapter = new LinkableProviderAdapter($provider);
        $result = $adapter->getItemById('5');

        $this->assertSame(['id' => '5', 'title' => 'Page Five', 'url' => '/page-5'], $result);
    }

    /**
     * getItemById がアイテム未存在時に null を返すこと
     */
    public function test_get_item_by_id_returns_null_when_not_found(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getItemById')->with('999')->willReturn(null);

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertNull($adapter->getItemById('999'));
    }

    /**
     * generateUrl がアイテムの URL を返すこと
     */
    public function test_generate_url_returns_item_url(): void
    {
        $dto = new LinkableDTO(
            id: '3',
            title: 'Contact',
            url: '/contact',
            type: 'page',
            source: 'test',
        );

        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getItemById')->with('3')->willReturn($dto);

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertSame('/contact', $adapter->generateUrl('3'));
    }

    /**
     * generateUrl がアイテム未存在時に null を返すこと
     */
    public function test_generate_url_returns_null_when_not_found(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getItemById')->with('999')->willReturn(null);

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertNull($adapter->generateUrl('999'));
    }

    /**
     * isPublished がアイテム存在時に true を返すこと
     */
    public function test_is_published_returns_true_when_item_exists(): void
    {
        $dto = new LinkableDTO(
            id: '1',
            title: 'Test',
            url: '/test',
            type: 'page',
            source: 'test',
        );

        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getItemById')->with('1')->willReturn($dto);

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertTrue($adapter->isPublished('1'));
    }

    /**
     * isPublished がアイテム未存在時に false を返すこと
     */
    public function test_is_published_returns_false_when_item_not_exists(): void
    {
        $provider = $this->createMock(LinkableProviderInterface::class);
        $provider->method('getItemById')->with('999')->willReturn(null);

        $adapter = new LinkableProviderAdapter($provider);

        $this->assertFalse($adapter->isPublished('999'));
    }
}
