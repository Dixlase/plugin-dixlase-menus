{{--
This file is part of Dixlase Menu.

Copyright (C) 2025 exc-D inc.
Website: https://exc-d.com

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

@extends('layouts.admin')

@section('title', __('dixlase-menu::admin.menus.edit.heading'))

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- ヘッダー -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
            <a href="{{ route('dixlase-menu::admin.menus.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                {{ __('dixlase-menu::admin.menus.index.heading') }}
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span>{{ $menu->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('dixlase-menu::admin.menus.edit.heading') }}: {{ $menu->name }}
            </h1>
            <a href="{{ route('dixlase-menu::admin.menus.delete', $menu->id) }}"
               class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                {{ __('common.delete') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 左カラム: メニュー基本情報 -->
        <div class="lg:col-span-1">
            <form id="menu-form" action="{{ route('dixlase-menu::admin.menus.update', $menu->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- 基本情報 -->
                    <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('dixlase-menu::admin.menus.edit.basic_info') }}
                        </h2>

                        <fieldset>
                            <legend class="sr-only">{{ __('dixlase-menu::admin.menus.edit.basic_info') }}</legend>

                            <div class="space-y-4">
                                <!-- メニュー名 -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menu::admin.menus.edit.name') }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $menu->name) }}"
                                           required
                                           class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- スラッグ -->
                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menu::admin.menus.edit.slug') }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="slug"
                                           name="slug"
                                           value="{{ old('slug', $menu->slug) }}"
                                           required
                                           pattern="[a-z0-9-_]+"
                                           class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('slug') border-red-500 @enderror">
                                    @error('slug')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 説明 -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menu::admin.menus.edit.description') }}
                                    </label>
                                    <textarea id="description"
                                              name="description"
                                              rows="3"
                                              class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description', $menu->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    <!-- 表示設定 -->
                    <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('dixlase-menu::admin.menus.edit.display_settings') }}
                        </h2>

                        <fieldset>
                            <legend class="sr-only">{{ __('dixlase-menu::admin.menus.edit.display_settings') }}</legend>

                            <div class="space-y-4">
                                <!-- 表示位置 -->
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menu::admin.menus.edit.location') }}
                                    </label>
                                    <select id="location"
                                            name="location"
                                            class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('location') border-red-500 @enderror">
                                        <option value="">{{ __('dixlase-menu::admin.menus.edit.select_location') }}</option>
                                        @foreach($availableLocations as $loc)
                                            <option value="{{ $loc['key'] }}" {{ old('location', $menu->location) == $loc['key'] ? 'selected' : '' }}>
                                                {{ $loc['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 表示順 -->
                                <div>
                                    <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menu::admin.menus.edit.display_order') }}
                                    </label>
                                    <input type="number"
                                           id="display_order"
                                           name="display_order"
                                           value="{{ old('display_order', $menu->display_order) }}"
                                           min="0"
                                           class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('display_order') border-red-500 @enderror">
                                    @error('display_order')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- アクティブ状態 -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               value="1"
                                               {{ old('is_active', $menu->is_active) ? 'checked' : '' }}
                                               class="w-4 h-4 text-indigo-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('dixlase-menu::admin.menus.edit.is_active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    <!-- 保存ボタン -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('common.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 右カラム: メニューアイテム管理 -->
        <div class="lg:col-span-2" x-data="menuItemsEditor()">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- ヘッダー -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('dixlase-menu::admin.menus.edit.menu_items') }}
                        </h2>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    @click="addItem()"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ __('dixlase-menu::admin.menus.edit.add_item') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- メニューアイテム一覧 -->
                <div class="p-6">
                    <!-- 空の状態 -->
                    <template x-if="items.length === 0">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                {{ __('dixlase-menu::admin.menus.edit.no_items') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menu::admin.menus.edit.no_items_description') }}
                            </p>
                            <div class="mt-6">
                                <button type="button"
                                        @click="addItem()"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('dixlase-menu::admin.menus.edit.add_first_item') }}
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- メニューアイテムリスト -->
                    <div id="menu-items-list" class="space-y-3" x-show="items.length > 0">
                        <template x-for="(item, index) in items" :key="item.id || index">
                            <div class="menu-item bg-gray-50 dark:bg-gray-700 rounded-md" :data-index="index">
                                <!-- 親メニュー -->
                                <div class="flex items-center gap-2 p-4">
                                    <!-- ドラッグハンドル -->
                                    <div class="drag-handle cursor-move p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                        </svg>
                                    </div>
                                    
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- タイトル -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menu::admin.menu_items.create.title') }}
                                            </label>
                                            <input type="text"
                                                   x-model="item.label"
                                                   placeholder="{{ __('dixlase-menu::admin.settings.menu_items.label_placeholder') }}"
                                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        
                                        <!-- URL -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menu::admin.menu_items.create.url') }}
                                            </label>
                                            <input type="text"
                                                   x-model="item.url"
                                                   placeholder="{{ __('dixlase-menu::admin.settings.menu_items.url_placeholder') }}"
                                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        
                                        <!-- ターゲット -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menu::admin.menu_items.create.target') }}
                                            </label>
                                            <select x-model="item.target"
                                                    class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="_self">{{ __('dixlase-menu::admin.settings.basic.target_self') }}</option>
                                                <option value="_blank">{{ __('dixlase-menu::admin.settings.basic.target_blank') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- アクションボタン -->
                                    <div class="flex items-center gap-1">
                                        <!-- 子メニュー追加ボタン -->
                                        <button type="button"
                                                @click="addChildItem(index)"
                                                x-show="item.depth < maxDepth - 1"
                                                class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="{{ __('dixlase-menu::admin.menus.edit.add_child') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- 削除ボタン -->
                                        <button type="button"
                                                @click="removeItem(index)"
                                                class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- 子メニュー -->
                                <template x-if="item.children && item.children.length > 0">
                                    <div class="children-list ml-8 border-l-2 border-gray-300 dark:border-gray-600 pl-4 pb-4 space-y-3" :data-parent-index="index">
                                        <template x-for="(child, childIndex) in item.children" :key="child.id || childIndex">
                                            <div class="child-menu-item flex items-center gap-2 p-3 bg-gray-100 dark:bg-gray-600 rounded-md">
                                                <!-- ドラッグハンドル -->
                                                <div class="child-drag-handle cursor-move p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                                    </svg>
                                                </div>
                                                
                                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    <!-- タイトル -->
                                                    <div>
                                                        <input type="text"
                                                               x-model="child.label"
                                                               placeholder="{{ __('dixlase-menu::admin.settings.menu_items.label_placeholder') }}"
                                                               class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                    </div>
                                                    
                                                    <!-- URL -->
                                                    <div>
                                                        <input type="text"
                                                               x-model="child.url"
                                                               placeholder="{{ __('dixlase-menu::admin.settings.menu_items.url_placeholder') }}"
                                                               class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                    </div>
                                                    
                                                    <!-- ターゲット -->
                                                    <div>
                                                        <select x-model="child.target"
                                                                class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                            <option value="_self">{{ __('dixlase-menu::admin.settings.basic.target_self') }}</option>
                                                            <option value="_blank">{{ __('dixlase-menu::admin.settings.basic.target_blank') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <!-- 削除ボタン -->
                                                <button type="button"
                                                        @click="removeChildItem(index, childIndex)"
                                                        class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- 保存ボタン -->
                    <div class="mt-6 flex items-center justify-between" x-show="items.length > 0 || hasChanges">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('dixlase-menu::admin.settings.menu_items.items_help') }}
                        </p>
                        <button type="button"
                                @click="saveItems()"
                                :disabled="saving"
                                class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                            <svg x-show="!saving" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg x-show="saving" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="saving ? '{{ __('common.saving') }}...' : '{{ __('common.save') }}'"></span>
                        </button>
                    </div>

                    <!-- 保存結果メッセージ -->
                    <div x-show="message" 
                         x-transition
                         :class="messageType === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'"
                         class="mt-4 p-4 rounded-md">
                        <p x-text="message"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SortableJS for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function menuItemsEditor() {
    return {
        items: @json($menuItems->map(function($item) {
            return [
                'id' => $item->id,
                'label' => $item->title,
                'url' => $item->url,
                'target' => $item->target,
                'source_type' => $item->source_type,
                'source_id' => $item->source_id,
                'depth' => 0,
                'children' => $item->children->map(function($child) {
                    return [
                        'id' => $child->id,
                        'label' => $child->title,
                        'url' => $child->url,
                        'target' => $child->target,
                        'source_type' => $child->source_type,
                        'source_id' => $child->source_id,
                        'depth' => 1,
                        'children' => [],
                    ];
                })->toArray(),
            ];
        })->toArray()),
        menuId: {{ $menu->id }},
        maxDepth: {{ $maxDepth }},
        syncUrl: '{{ route("dixlase-menu::admin.menus.items.sync", $menu->id) }}',
        saving: false,
        message: '',
        messageType: 'success',
        hasChanges: false,
        sortableInstance: null,
        
        init() {
            this.$nextTick(() => {
                this.initSortable();
            });
            
            // 変更を監視
            this.$watch('items', () => {
                this.hasChanges = true;
            }, { deep: true });
        },
        
        initSortable() {
            const menuList = document.getElementById('menu-items-list');
            if (menuList && typeof Sortable !== 'undefined') {
                this.sortableInstance = new Sortable(menuList, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: (evt) => {
                        const item = this.items.splice(evt.oldIndex, 1)[0];
                        this.items.splice(evt.newIndex, 0, item);
                    }
                });
                
                this.initChildSortables();
            }
        },
        
        initChildSortables() {
            this.$nextTick(() => {
                document.querySelectorAll('.children-list').forEach((childList) => {
                    if (!childList._sortable) {
                        const parentIndex = parseInt(childList.dataset.parentIndex);
                        childList._sortable = new Sortable(childList, {
                            handle: '.child-drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: (evt) => {
                                if (this.items[parentIndex] && this.items[parentIndex].children) {
                                    const child = this.items[parentIndex].children.splice(evt.oldIndex, 1)[0];
                                    this.items[parentIndex].children.splice(evt.newIndex, 0, child);
                                }
                            }
                        });
                    }
                });
            });
        },
        
        generateId() {
            return 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },
        
        addItem() {
            this.items.push({
                id: this.generateId(),
                label: '',
                url: '',
                target: '_self',
                source_type: 'custom_url',
                source_id: null,
                depth: 0,
                children: []
            });
            this.$nextTick(() => this.initSortable());
        },
        
        removeItem(index) {
            this.items.splice(index, 1);
        },
        
        addChildItem(parentIndex) {
            if (!this.items[parentIndex].children) {
                this.items[parentIndex].children = [];
            }
            this.items[parentIndex].children.push({
                id: this.generateId(),
                label: '',
                url: '',
                target: '_self',
                source_type: 'custom_url',
                source_id: null,
                depth: 1,
                children: []
            });
            this.$nextTick(() => this.initChildSortables());
        },
        
        removeChildItem(parentIndex, childIndex) {
            this.items[parentIndex].children.splice(childIndex, 1);
        },
        
        async saveItems() {
            this.saving = true;
            this.message = '';
            
            try {
                const response = await fetch(this.syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ items: this.items })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    this.messageType = 'success';
                    this.hasChanges = false;
                    
                    // 返されたアイテムで更新（IDが割り当てられる）
                    if (data.items) {
                        this.items = data.items;
                    }
                } else {
                    this.message = data.message || '{{ __("dixlase-menu::admin.messages.menu_items_save_failed") }}';
                    this.messageType = 'error';
                }
            } catch (error) {
                console.error('Save error:', error);
                this.message = '{{ __("dixlase-menu::admin.messages.menu_items_save_failed") }}';
                this.messageType = 'error';
            } finally {
                this.saving = false;
                
                // メッセージを5秒後に消す
                setTimeout(() => {
                    this.message = '';
                }, 5000);
            }
        }
    }
}
</script>
@endpush
