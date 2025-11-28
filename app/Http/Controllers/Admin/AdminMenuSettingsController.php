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

namespace Plugins\DixlaseMenu\App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Traits\AdminInterfaceTrait;
use App\Traits\AdminLoggedInTrait;
use App\Contracts\PluginIntegration\LinkableProviderInterface;
use Plugins\DixlaseMenu\App\Contracts\Repositories\MenuSettingRepositoryInterface;
use Plugins\DixlaseMenu\App\Http\Requests\AdminMenuSettingsUpdateRequest;

/**
 * メニュー設定管理コントローラー
 */
class AdminMenuSettingsController extends Controller
{
    use AdminInterfaceTrait;
    use AdminLoggedInTrait;

    /**
     * コンストラクタ
     */
    public function __construct(
        private MenuSettingRepositoryInterface $settingRepository
    ) {
        $this->initialize();
        $this->initializeAfterLogin();
    }

    /**
     * 設定画面表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = [
            'default_target' => $this->settingRepository->get('default_target', '_self'),
            'menu_items' => $this->settingRepository->getJson('menu_items', []),
        ];

        // LinkableProviderを取得
        $linkableProviders = $this->getLinkableProviders();

        $this->viewParams['settings'] = $settings;
        $this->viewParams['linkableProviders'] = $linkableProviders;

        return view('dixlase-menu::admin.settings.menus.index', $this->viewParams);
    }

    /**
     * 登録されているLinkableProviderを取得
     *
     * @return array
     */
    protected function getLinkableProviders(): array
    {
        $providers = [];

        try {
            $taggedProviders = app()->tagged('linkable.providers');

            foreach ($taggedProviders as $provider) {
                if ($provider instanceof LinkableProviderInterface && $provider->isAvailable()) {
                    $items = $provider->getAvailableItems();

                    $providers[] = [
                        'key' => $provider->getProviderKey(),
                        'label' => $provider->getProviderLabel(),
                        'icon' => $provider->getProviderIcon(),
                        'items' => array_map(fn($item) => $item->toArray(), $items),
                    ];
                }
            }
        } catch (\Exception $e) {
            // プロバイダーが登録されていない場合は空配列
            \Log::debug('LinkableProviders not found: ' . $e->getMessage());
        }

        return $providers;
    }

    /**
     * 設定更新
     *
     * @param AdminMenuSettingsUpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AdminMenuSettingsUpdateRequest $request)
    {        
        $validated = $request->validated();

        // デフォルトターゲットを保存
        $this->settingRepository->set(
            'default_target',
            $validated['default_target'] ?? '_self',
            'string',
            'デフォルトのリンクターゲット'
        );

        // メニューアイテムを保存
        if (isset($validated['menu_items'])) {
            $menuItems = $this->sanitizeMenuItems($validated['menu_items']);

            $this->settingRepository->set(
                'menu_items',
                $menuItems,
                'json',
                'メニューアイテム'
            );
        }

        // キャッシュをクリア
        $this->settingRepository->clearCache();

        return redirect()
            ->back()
            ->with('success', __('dixlase-menu::admin.messages.settings_updated'));
    }

    /**
     * メニューアイテムをサニタイズ
     *
     * @param array $items
     * @return array
     */
    protected function sanitizeMenuItems(array $items): array
    {
        return array_values(array_filter(array_map(function ($item) {
            if (empty($item['label'])) {
                return null;
            }

            $sanitized = [
                'id' => $item['id'] ?? uniqid('menu_'),
                'label' => $item['label'],
                'url' => $item['url'] ?? '',
                'target' => $item['target'] ?? '_self',
                'source_type' => $item['source_type'] ?? 'custom',
                'source_id' => $item['source_id'] ?? null,
                'source_provider' => $item['source_provider'] ?? null,
                'children' => [],
            ];

            // 子メニューを処理
            if (!empty($item['children']) && is_array($item['children'])) {
                $sanitized['children'] = array_values(array_filter(array_map(function ($child) {
                    if (empty($child['label'])) {
                        return null;
                    }

                    return [
                        'id' => $child['id'] ?? uniqid('child_'),
                        'label' => $child['label'],
                        'url' => $child['url'] ?? '',
                        'target' => $child['target'] ?? '_self',
                        'source_type' => $child['source_type'] ?? 'custom',
                        'source_id' => $child['source_id'] ?? null,
                        'source_provider' => $child['source_provider'] ?? null,
                    ];
                }, $item['children'])));
            }

            return $sanitized;
        }, $items)));
    }

    /**
     * キャッシュクリア
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        $this->settingRepository->clearCache();

        return redirect()
            ->back()
            ->with('success', __('dixlase-menu::admin.messages.cache_cleared'));
    }
}
