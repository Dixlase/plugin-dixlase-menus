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

namespace Plugins\DixlaseMenus\App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Traits\AdminInterfaceTrait;
use App\Traits\AdminLoggedInTrait;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenus\App\Contracts\Repositories\MenuSettingRepositoryInterface;
use Plugins\DixlaseMenus\App\Http\Requests\AdminMenuStoreRequest;
use Plugins\DixlaseMenus\App\Http\Requests\AdminMenuUpdateRequest;
use Plugins\DixlaseMenus\App\Enums\MenuLocation;
use Plugins\DixlaseMenus\App\Enums\PlacementType;

/**
 * メニュー管理コントローラー
 */
class AdminMenuController extends Controller
{
    use AdminInterfaceTrait;
    use AdminLoggedInTrait;

    /**
     * コンストラクタ
     */
    public function __construct(
        private MenuRepositoryInterface $menuRepository,
        private MenuSettingRepositoryInterface $settingRepository
    ) {
        $this->initialize();
        $this->initializeAfterLogin();
    }

    /**
     * メニュー一覧表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $menus = $this->menuRepository->all();
        $locationOptions = MenuLocation::options();

        $this->viewParams['menus'] = $menus;
        $this->viewParams['locationOptions'] = $locationOptions;

        return view('dixlase-menus::admin.menus.index', $this->viewParams);
    }

    /**
     * メニュー作成フォーム表示
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->viewParams['locationOptions'] = MenuLocation::options();
        $this->viewParams['placementTypeOptions'] = PlacementType::getRadioCardOptions();

        return view('dixlase-menus::admin.menus.create', $this->viewParams);
    }

    /**
     * メニュー保存
     *
     * @param AdminMenuStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AdminMenuStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['lang'] = app()->getLocale();

        $menu = $this->menuRepository->create($validated);

        return redirect()
            ->route('dixlase-menus::admin.menus.edit', $menu->id)
            ->with('success', __('dixlase-menus::admin.messages.menu_created'));
    }

    /**
     * メニュー編集フォーム表示
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $menu = $this->menuRepository->findWithItems($id);

        if (!$menu) {
            abort(404);
        }

        $maxDepth = $this->settingRepository->getInteger('max_menu_depth', 3);

        // ルートレベルのメニューアイテムをフロントエンド用配列に変換
        $menuItems = $menu->items()
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'label' => $item->title,
                'url' => $item->url,
                'target' => $item->target,
                'source_type' => $item->source_type,
                'source_id' => $item->source_id,
                'depth' => 0,
                'children' => $item->children->map(fn ($child) => [
                    'id' => $child->id,
                    'label' => $child->title,
                    'url' => $child->url,
                    'target' => $child->target,
                    'source_type' => $child->source_type,
                    'source_id' => $child->source_id,
                    'depth' => 1,
                    'children' => [],
                ])->toArray(),
            ])
            ->toArray();

        $this->viewParams['menu'] = $menu;
        $this->viewParams['menuItems'] = $menuItems;
        $this->viewParams['locationOptions'] = MenuLocation::options();
        $this->viewParams['placementTypeOptions'] = PlacementType::options();
        $this->viewParams['placementTypeDescriptions'] = PlacementType::descriptions();
        $this->viewParams['maxDepth'] = $maxDepth;
        $this->viewParams['linkSourcesUrl'] = route('dixlase-menus::admin.menus.link-sources.index');

        return view('dixlase-menus::admin.menus.edit', $this->viewParams);
    }

    /**
     * メニュー更新
     *
     * @param AdminMenuUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AdminMenuUpdateRequest $request, int $id)
    {
        $validated = $request->validated();

        $menu = $this->menuRepository->update($id, $validated);

        return redirect()
            ->back()
            ->with('success', __('dixlase-menus::admin.messages.menu_updated'));
    }

    /**
     * メニュー削除実行
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $menu = $this->menuRepository->find($id);

        if (!$menu) {
            return redirect()
                ->route('dixlase-menus::admin.menus.index')
                ->withErrors(['error' => __('dixlase-menus::admin.messages.menu_not_found')]);
        }

        // ソフトデリート
        $this->menuRepository->softDelete($id);

        return redirect()
            ->route('dixlase-menus::admin.menus.index')
            ->with('success', __('dixlase-menus::admin.messages.menu_deleted'));
    }

    /**
     * メニュー復元
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(int $id)
    {
        $this->menuRepository->restore($id);

        return redirect()
            ->route('dixlase-menus::admin.menus.index')
            ->with('success', __('dixlase-menus::admin.messages.menu_restored'));
    }

}
