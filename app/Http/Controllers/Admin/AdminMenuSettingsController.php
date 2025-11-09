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
            'max_menu_depth' => $this->settingRepository->getInteger('max_menu_depth', 3),
            'enable_menu_cache' => $this->settingRepository->getBoolean('enable_menu_cache', true),
            'cache_duration' => $this->settingRepository->getInteger('cache_duration', 3600),
            'default_target' => $this->settingRepository->get('default_target', '_self'),
            'available_locations' => $this->settingRepository->getJson('available_locations', []),
        ];

        $this->viewParams['settings'] = $settings;

        return view('dixlase-menu::admin.settings.index', $this->viewParams);
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

        // 各設定を保存
        $this->settingRepository->set(
            'max_menu_depth',
            $validated['max_menu_depth'],
            'integer',
            'メニューの最大階層深度'
        );

        $this->settingRepository->set(
            'enable_menu_cache',
            $validated['enable_menu_cache'] ?? false,
            'boolean',
            'メニューキャッシュの有効化'
        );

        $this->settingRepository->set(
            'cache_duration',
            $validated['cache_duration'],
            'integer',
            'キャッシュ有効期間（秒）'
        );

        $this->settingRepository->set(
            'default_target',
            $validated['default_target'],
            'string',
            'デフォルトのリンクターゲット'
        );

        // available_locationsは配列として保存
        if (isset($validated['available_locations'])) {
            $locations = array_filter($validated['available_locations'], function ($location) {
                return !empty($location['key']) && !empty($location['label']);
            });

            $this->settingRepository->set(
                'available_locations',
                $locations,
                'json',
                '利用可能なメニュー位置'
            );
        }

        // キャッシュをクリア
        $this->settingRepository->clearCache();

        return redirect()
            ->back()
            ->with('success', __('dixlase-menu::admin.messages.settings_updated'));
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
