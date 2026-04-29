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

namespace Plugins\DixlaseMenus\Tests\Feature;

use App\Contracts\PluginIntegration\LinkableProviderInterface;
use App\Helpers\PluginHelper;
use ReflectionClass;
use Tests\TestCase;

/**
 * linkable capability に基づく LinkableProvider 検出フィルタのテスト
 *
 * DixlaseMenusServiceProvider::registerLinkSources() で適用される
 * capability フィルタの動作を検証する。
 *
 * フィルタロジックそのものを直接検証する方式（リフレクションでの再 boot は
 * シングルトンの解決タイミングと衝突するため、フィルタの判定式を独立に検証）。
 */
class MenuLinkableSourceFilterTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->setCapabilityCache(null);
        parent::tearDown();
    }

    /**
     * linkable capability を宣言したプラグインのプロバイダーは通過すること
     */
    public function test_provider_with_linkable_capability_passes_filter(): void
    {
        $this->setCapabilityCache([
            'test-with-cap' => ['linkable'],
        ]);

        $this->assertTrue(
            PluginHelper::pluginHasCapability('test-with-cap', 'linkable')
        );
    }

    /**
     * linkable capability を宣言していないプラグインのプロバイダーは除外されること
     */
    public function test_provider_without_linkable_capability_is_filtered_out(): void
    {
        $this->setCapabilityCache([
            'test-without-cap' => ['seo-meta'], // 他の capability はあるが linkable は未宣言
        ]);

        $this->assertFalse(
            PluginHelper::pluginHasCapability('test-without-cap', 'linkable')
        );
    }

    /**
     * 有効化されていない（capability マップに含まれない）プラグインは除外されること
     */
    public function test_disabled_plugin_is_filtered_out(): void
    {
        $this->setCapabilityCache([]); // 有効化プラグインなし

        $this->assertFalse(
            PluginHelper::pluginHasCapability('orphan-plugin', 'linkable')
        );
    }

    /**
     * 実際の DixlasePages プラグインは linkable capability を宣言していること
     *
     * Phase 1 で plugin.json に追加した宣言が PluginHelper 経由で読み取れることを保証する。
     */
    public function test_dixlase_pages_declares_linkable_capability(): void
    {
        $this->setCapabilityCache(null); // 実データ読み込み

        // plugins テーブル経由で読まれる前提のため、有効化されていない場合はスキップ
        if (! PluginHelper::isEnabled('dixlase-pages')) {
            $this->markTestSkipped('dixlase-pages plugin is not enabled in this test environment');
        }

        $this->assertTrue(
            PluginHelper::pluginHasCapability('dixlase-pages', 'linkable'),
            'DixlasePages should declare linkable capability in plugin.json'
        );
    }

    /**
     * 実際の DixlaseLegal プラグインは linkable capability を宣言していること
     */
    public function test_dixlase_legal_declares_linkable_capability(): void
    {
        $this->setCapabilityCache(null);

        if (! PluginHelper::isEnabled('dixlase-legal')) {
            $this->markTestSkipped('dixlase-legal plugin is not enabled in this test environment');
        }

        $this->assertTrue(
            PluginHelper::pluginHasCapability('dixlase-legal', 'linkable'),
            'DixlaseLegal should declare linkable capability in plugin.json'
        );
    }

    /**
     * 想定するフィルタ条件式（production と同等）が動作すること
     *
     * registerLinkSources 内のフィルタ:
     *   if (! PluginHelper::pluginHasCapability($provider->getProviderKey(), 'linkable')) { continue; }
     */
    public function test_filter_logic_excludes_provider_when_capability_not_declared(): void
    {
        $this->setCapabilityCache([
            'cap-yes' => ['linkable'],
            'cap-no' => [],
        ]);

        $providerYes = $this->createMock(LinkableProviderInterface::class);
        $providerYes->method('getProviderKey')->willReturn('cap-yes');

        $providerNo = $this->createMock(LinkableProviderInterface::class);
        $providerNo->method('getProviderKey')->willReturn('cap-no');

        $providers = [$providerYes, $providerNo];
        $accepted = array_filter(
            $providers,
            fn (LinkableProviderInterface $p) => PluginHelper::pluginHasCapability($p->getProviderKey(), 'linkable')
        );

        $acceptedKeys = array_map(fn ($p) => $p->getProviderKey(), array_values($accepted));

        $this->assertSame(['cap-yes'], $acceptedKeys);
    }

    /**
     * PluginHelper の有効化 capability キャッシュをリフレクション経由で設定
     */
    private function setCapabilityCache(?array $cache): void
    {
        $reflection = new ReflectionClass(PluginHelper::class);
        $property = $reflection->getProperty('enabledCapabilityCache');
        $property->setAccessible(true);
        $property->setValue(null, $cache);
    }
}
