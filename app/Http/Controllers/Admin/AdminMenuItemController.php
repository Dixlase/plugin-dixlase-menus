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
use Plugins\DixlaseMenu\App\Contracts\Repositories\MenuItemRepositoryInterface;
use Plugins\DixlaseMenu\App\Contracts\Repositories\MenuRepositoryInterface;
use Plugins\DixlaseMenu\App\Contracts\Repositories\MenuSettingRepositoryInterface;
use Plugins\DixlaseMenu\App\Services\MenuLinkSourceManager;
use Plugins\DixlaseMenu\App\Services\MenuService;
use Plugins\DixlaseMenu\App\Http\Requests\AdminMenuItemStoreRequest;
use Plugins\DixlaseMenu\App\Http\Requests\AdminMenuItemUpdateRequest;

/**
 * メニューアイテム管理コントローラー
 */
class AdminMenuItemController extends Controller
{
    use AdminInterfaceTrait;
    use AdminLoggedInTrait;

    /**
     * コンストラクタ
     */
    public function __construct(
        private MenuItemRepositoryInterface $menuItemRepository,
        private MenuRepositoryInterface $menuRepository,
        private MenuSettingRepositoryInterface $settingRepository,
        private MenuLinkSourceManager $linkSourceManager,
        private MenuService $menuService
    ) {
        $this->initialize();
        $this->initializeAfterLogin();
    }

    /**
     * メニューアイテム作成フォーム表示
     *
     * @param int $menuId
     * @param int|null $parentId
     * @return \Illuminate\View\View
     */
    public function create(int $menuId, ?int $parentId = null)
    {
        $menu = $this->menuRepository->find($menuId);

        if (!$menu) {
            abort(404);
        }

        $parentItem = $parentId ? $this->menuItemRepository->find($parentId) : null;
        $maxDepth = $this->settingRepository->getInteger('max_menu_depth', 3);
        
        // リンクソースを取得（ラベル付き配列）
        $linkSources = [];
        foreach ($this->linkSourceManager->all() as $key => $source) {
            $linkSources[$key] = $source->getLabel();
        }

        $this->viewParams['menu'] = $menu;
        $this->viewParams['parentItem'] = $parentItem;
        $this->viewParams['maxDepth'] = $maxDepth;
        $this->viewParams['linkSources'] = $linkSources;

        return view('dixlase-menu::admin.menus.items.create', $this->viewParams);
    }

    /**
     * メニューアイテム保存
     *
     * @param AdminMenuItemStoreRequest $request
     * @param int $menuId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AdminMenuItemStoreRequest $request, int $menuId)
    {
        $validated = $request->validated();
        $validated['menu_id'] = $menuId;

        // 深さチェック
        $maxDepth = $this->settingRepository->getInteger('max_menu_depth', 3);
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = $this->menuItemRepository->find($validated['parent_id']);
            if ($parent && $parent->depth >= $maxDepth - 1) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['parent_id' => __('dixlase-menu::admin.messages.max_depth_exceeded')]);
            }
        }

        $menuItem = $this->menuItemRepository->create($validated);

        return redirect()
            ->route('dixlase-menu::admin.menus.edit', $menuId)
            ->with('success', __('dixlase-menu::admin.messages.menu_item_created'));
    }

    /**
     * メニューアイテム編集フォーム表示
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $menuItem = $this->menuItemRepository->find($id);

        if (!$menuItem) {
            abort(404);
        }

        $menu = $this->menuRepository->find($menuItem->menu_id);
        $maxDepth = $this->settingRepository->getInteger('max_menu_depth', 3);
        
        // リンクソースを取得（ラベル付き配列）
        $linkSources = [];
        foreach ($this->linkSourceManager->all() as $key => $source) {
            $linkSources[$key] = $source->getLabel();
        }

        $this->viewParams['menuItem'] = $menuItem;
        $this->viewParams['menu'] = $menu;
        $this->viewParams['maxDepth'] = $maxDepth;
        $this->viewParams['linkSources'] = $linkSources;

        return view('dixlase-menu::admin.menus.items.edit', $this->viewParams);
    }

    /**
     * メニューアイテム更新
     *
     * @param AdminMenuItemUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AdminMenuItemUpdateRequest $request, int $id)
    {
        $menuItem = $this->menuItemRepository->find($id);

        if (!$menuItem) {
            return redirect()
                ->route('dixlase-menu::admin.menus.index')
                ->withErrors(['error' => __('dixlase-menu::admin.messages.menu_item_not_found')]);
        }

        $validated = $request->validated();

        // 親が変更された場合の深さチェック
        if (isset($validated['parent_id']) && $validated['parent_id'] !== $menuItem->parent_id) {
            $maxDepth = $this->settingRepository->getInteger('max_menu_depth', 3);
            
            if ($validated['parent_id']) {
                $newParent = $this->menuItemRepository->find($validated['parent_id']);
                
                // 自分自身を親にできない
                if ($newParent && $newParent->id === $menuItem->id) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['parent_id' => __('dixlase-menu::admin.messages.cannot_set_self_as_parent')]);
                }
                
                // 子孫を親にできない
                if ($newParent && $newParent->isDescendantOf($menuItem)) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['parent_id' => __('dixlase-menu::admin.messages.cannot_set_descendant_as_parent')]);
                }
                
                // 深さチェック
                if ($newParent && $newParent->depth >= $maxDepth - 1) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['parent_id' => __('dixlase-menu::admin.messages.max_depth_exceeded')]);
                }
            }
        }

        $this->menuItemRepository->update($id, $validated);

        return redirect()
            ->route('dixlase-menu::admin.menus.edit', $menuItem->menu_id)
            ->with('success', __('dixlase-menu::admin.messages.menu_item_updated'));
    }

    /**
     * メニューアイテム削除
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $menuItem = $this->menuItemRepository->find($id);

        if (!$menuItem) {
            return redirect()
                ->route('dixlase-menu::admin.menus.index')
                ->withErrors(['error' => __('dixlase-menu::admin.messages.menu_item_not_found')]);
        }

        $menuId = $menuItem->menu_id;

        // 子アイテムがある場合は確認
        if ($this->menuItemRepository->hasChildren($id)) {
            // 子孫も含めて削除
            $this->menuItemRepository->deleteWithDescendants($id);
        } else {
            $this->menuItemRepository->delete($id);
        }

        return redirect()
            ->route('dixlase-menu::admin.menus.edit', $menuId)
            ->with('success', __('dixlase-menu::admin.messages.menu_item_deleted'));
    }

    /**
     * メニューアイテムの並び順を更新（Ajax）
     *
     * @param Request $request
     * @param int $menuId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request, int $menuId)
    {
        $items = $request->input('items', []);

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => __('dixlase-menu::admin.messages.no_items_to_update'),
            ], 400);
        }

        $result = $this->menuItemRepository->updateOrder($items);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => __('dixlase-menu::admin.messages.order_updated'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('dixlase-menu::admin.messages.order_update_failed'),
        ], 500);
    }

    /**
     * メニューアイテムを移動（Ajax）
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function move(Request $request, int $id)
    {
        $newParentId = $request->input('parent_id');

        try {
            $menuItem = $this->menuItemRepository->moveToParent($id, $newParentId);

            return response()->json([
                'success' => true,
                'message' => __('dixlase-menu::admin.messages.menu_item_moved'),
                'item' => $menuItem,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('dixlase-menu::admin.messages.move_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * メニューアイテムを一括保存（Ajax）
     *
     * @param Request $request
     * @param int $menuId
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncItems(Request $request, int $menuId)
    {
        $menu = $this->menuRepository->find($menuId);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => __('dixlase-menu::admin.messages.menu_not_found'),
            ], 404);
        }

        $items = $request->input('items', []);

        $result = $this->menuService->syncMenuItems($menuId, $items);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => __('dixlase-menu::admin.messages.menu_items_saved'),
                'items' => $this->menuService->getMenuHierarchy($menuId),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('dixlase-menu::admin.messages.menu_items_save_failed'),
        ], 500);
    }

    /**
     * メニューアイテムを取得（Ajax）
     *
     * @param int $menuId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItems(int $menuId)
    {
        $menu = $this->menuRepository->find($menuId);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => __('dixlase-menu::admin.messages.menu_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'items' => $this->menuService->getMenuHierarchy($menuId),
        ]);
    }
}
