{{--
This file is part of Dixlase Menus.

Copyright (C) 2026 exc-D inc. and Dixlase contributors
https://exc-d.com

Dixlase Menus is dual-licensed. You may use this file under either:

  (a) the GNU General Public License version 3 or later, as published
      by the Free Software Foundation; or

  (b) a commercial license agreement obtained from exc-D inc.

Unless you have entered into a commercial license agreement, this
file is governed by the GPL terms below.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
--}}

<div class="menu-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md p-4 hover:shadow-md transition-shadow duration-150"
     data-id="{{ $item->id }}"
     data-depth="{{ $item->depth }}">
    
    <div class="flex items-center gap-3">
        <!-- ドラッグハンドル -->
        <div class="drag-handle cursor-move">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
            </svg>
        </div>

        <!-- アイテム情報 -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <!-- タイトル -->
                <h3 class="menu-item-title text-sm font-medium truncate {{ $item->is_active ? '' : 'text-gray-400 dark:text-gray-500' }}">
                    {{ $item->title }}
                </h3>

                <!-- バッジ -->
                @if(!$item->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ __('common.inactive') }}
                    </span>
                @endif

                @if($item->children_count > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $item->children_count }} {{ __('dixlase-menus::admin.menus.edit.children') }}
                    </span>
                @endif
            </div>

            <!-- メタ情報 -->
            <div class="menu-item-meta flex items-center gap-3 mt-1">
                <span class="text-xs">
                    @if($item->source_type === 'custom_url')
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        {{ Str::limit($item->url, 40) }}
                    @else
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        {{ ucfirst($item->source_type) }} #{{ $item->source_id }}
                    @endif
                </span>

                @if($item->target)
                    <span class="text-xs">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        {{ $item->target }}
                    </span>
                @endif

                <span class="text-xs">
                    {{ __('dixlase-menus::admin.menus.edit.order') }}: {{ $item->display_order }}
                </span>
            </div>
        </div>

        <!-- アクションボタン -->
        <div class="menu-item-actions flex items-center gap-2">
            <!-- 子アイテム追加 -->
            @if($item->depth < $maxDepth - 1)
                <a href="{{ route('dixlase-menus::admin.menus.items.create.child', [$item->menu_id, $item->id]) }}"
                   class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                   title="{{ __('dixlase-menus::admin.menus.edit.add_child') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </a>
            @endif

            <!-- 展開/折りたたみ -->
            @if($item->children_count > 0)
                <button type="button"
                        class="toggle-children-btn p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                        title="{{ __('dixlase-menus::admin.menus.edit.toggle_children') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif

            <!-- 編集 -->
            <a href="{{ route('dixlase-menus::admin.menus.items.edit', $item->id) }}"
               class="p-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
               title="{{ __('common.edit') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </a>

            <!-- 削除 -->
            <button type="button"
                    class="delete-item-btn p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                    data-item-id="{{ $item->id }}"
                    data-item-title="{{ $item->title }}"
                    title="{{ __('common.delete') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- 子アイテム -->
    @if($item->children && $item->children->isNotEmpty())
        <div class="menu-item-children mt-3 ml-6 space-y-2">
            @foreach($item->children as $child)
                @include('dixlase-menus::admin.menus.partials.menu-item', ['item' => $child])
            @endforeach
        </div>
    @endif
</div>
