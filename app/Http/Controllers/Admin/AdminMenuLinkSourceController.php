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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Traits\AdminInterfaceTrait;
use App\Traits\AdminLoggedInTrait;
use Plugins\DixlaseMenus\App\Services\MenuLinkSourceManager;

/**
 * メニューリンクソースAPIコントローラー
 *
 * リンクソース一覧とソースごとのアイテム取得を提供する
 */
class AdminMenuLinkSourceController extends Controller
{
    use AdminInterfaceTrait;
    use AdminLoggedInTrait;

    public function __construct(
        private MenuLinkSourceManager $linkSourceManager,
    ) {
        $this->initialize();
        $this->initializeAfterLogin();
    }

    /**
     * 利用可能なリンクソース一覧を取得
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'sources' => $this->linkSourceManager->getSourcesMetadata(),
        ]);
    }

    /**
     * 特定ソースのアイテム一覧を取得
     */
    public function items(Request $request, string $sourceType): JsonResponse
    {
        $source = $this->linkSourceManager->getSource($sourceType);

        if (!$source || !$source->isAvailable()) {
            return response()->json(['error' => 'Source not found'], 404);
        }

        $search = $request->query('search', '');

        $items = $search !== ''
            ? $source->searchItems($search)
            : $source->getAvailableItems();

        return response()->json([
            'items' => $items,
        ]);
    }
}
