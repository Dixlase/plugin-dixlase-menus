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

@section('title', __('dixlase-menu::admin.settings.heading'))

@section('content')
    <form id="menu-settings-form" action="{{ route('dixlase-menu::admin.settings.menus.update') }}" method="POST">
        @csrf
        
        <!-- メニューアイテム設定 -->
        <section class="mb-8" x-data="menuItems()">
            <h2 class="text-xl font-semibold mb-4">{{ __('dixlase-menu::admin.settings.menu_items.title') }}</h2>
            
            <fieldset>
                <legend>{{ __('dixlase-menu::admin.settings.menu_items.title') }}</legend>
                
                <!-- メニューリスト（ドラッグ＆ドロップ対応） -->
                <div id="menu-list" class="space-y-4">
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
                                
                                <div class="flex-1 space-y-3">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- ラベル（デフォルト） -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menu::admin.settings.menu_items.label') }}
                                            </label>
                                            <input type="text"
                                                   :name="'menu_items[' + index + '][label]'"
                                                   x-model="item.label"
                                                   placeholder="{{ __('dixlase-menu::admin.settings.menu_items.label_placeholder') }}"
                                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                   required>
                                        </div>
                                        
                                        <!-- URL / コンテンツ選択 -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menu::admin.settings.menu_items.url') }}
                                            </label>
                                            <div class="flex gap-2">
                                                <input type="text"
                                                       :name="'menu_items[' + index + '][url]'"
                                                       x-model="item.url"
                                                       placeholder="{{ __('dixlase-menu::admin.settings.menu_items.url_placeholder') }}"
                                                       class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                @if(!empty($linkableProviders))
                                                <button type="button"
                                                        @click="openContentSelector(index, null)"
                                                        class="px-3 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-md transition-colors"
                                                        title="{{ __('dixlase-menu::admin.settings.menu_items.source_select') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                                                    </svg>
                                                </button>
                                                @endif
                                            </div>
                                            <!-- 隠しフィールド：ソース情報 -->
                                            <input type="hidden" :name="'menu_items[' + index + '][source_type]'" x-model="item.source_type">
                                            <input type="hidden" :name="'menu_items[' + index + '][source_id]'" x-model="item.source_id">
                                            <input type="hidden" :name="'menu_items[' + index + '][source_provider]'" x-model="item.source_provider">
                                        </div>
                                        
                                        <!-- ターゲット -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menu::admin.settings.menu_items.target') }}
                                            </label>
                                            <select :name="'menu_items[' + index + '][target]'"
                                                    x-model="item.target"
                                                    class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="_self">{{ __('dixlase-menu::admin.settings.basic.target_self') }}</option>
                                                <option value="_blank">{{ __('dixlase-menu::admin.settings.basic.target_blank') }}</option>
                                                <option value="_parent">{{ __('dixlase-menu::admin.settings.basic.target_parent') }}</option>
                                                <option value="_top">{{ __('dixlase-menu::admin.settings.basic.target_top') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                <!-- アクションボタン -->
                                <div class="flex items-center gap-1">
                                    <!-- 子メニュー追加ボタン -->
                                    <button type="button"
                                            @click="addChildItem(index)"
                                            class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="{{ __('dixlase-menu::admin.settings.menu_items.add_child') }}">
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
                                            
                                            <div class="flex-1 space-y-2">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    <!-- ラベル -->
                                                    <div>
                                                        <input type="text"
                                                               :name="'menu_items[' + index + '][children][' + childIndex + '][label]'"
                                                               x-model="child.label"
                                                               placeholder="{{ __('dixlase-menu::admin.settings.menu_items.label_placeholder') }}"
                                                               class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                               required>
                                                    </div>
                                                    
                                                    <!-- URL -->
                                                    <div>
                                                        <div class="flex gap-1">
                                                            <input type="text"
                                                                   :name="'menu_items[' + index + '][children][' + childIndex + '][url]'"
                                                                   x-model="child.url"
                                                                   placeholder="{{ __('dixlase-menu::admin.settings.menu_items.url_placeholder') }}"
                                                                   class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                            @if(!empty($linkableProviders))
                                                            <button type="button"
                                                                    @click="openContentSelector(index, childIndex)"
                                                                    class="px-2 py-1.5 bg-gray-200 dark:bg-gray-500 hover:bg-gray-300 dark:hover:bg-gray-400 text-gray-700 dark:text-gray-200 rounded-md transition-colors"
                                                                    title="{{ __('dixlase-menu::admin.settings.menu_items.source_select') }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                                                                </svg>
                                                            </button>
                                                            @endif
                                                        </div>
                                                        <!-- 隠しフィールド：ソース情報 -->
                                                        <input type="hidden" :name="'menu_items[' + index + '][children][' + childIndex + '][source_type]'" x-model="child.source_type">
                                                        <input type="hidden" :name="'menu_items[' + index + '][children][' + childIndex + '][source_id]'" x-model="child.source_id">
                                                        <input type="hidden" :name="'menu_items[' + index + '][children][' + childIndex + '][source_provider]'" x-model="child.source_provider">
                                                    </div>
                                                    
                                                    <!-- ターゲット -->
                                                    <div>
                                                        <select :name="'menu_items[' + index + '][children][' + childIndex + '][target]'"
                                                                x-model="child.target"
                                                                class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                            <option value="_self">{{ __('dixlase-menu::admin.settings.basic.target_self') }}</option>
                                                            <option value="_blank">{{ __('dixlase-menu::admin.settings.basic.target_blank') }}</option>
                                                            <option value="_parent">{{ __('dixlase-menu::admin.settings.basic.target_parent') }}</option>
                                                            <option value="_top">{{ __('dixlase-menu::admin.settings.basic.target_top') }}</option>
                                                        </select>
                                                    </div>
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

                <!-- 親メニュー追加ボタン -->
                <div class="mt-4">
                    <button type="button"
                            @click="addItem()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('dixlase-menu::admin.settings.menu_items.add_item') }}
                    </button>
                </div>

                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('dixlase-menu::admin.settings.menu_items.items_help') }}
                </p>
            </fieldset>
            
            <!-- コンテンツ選択モーダル -->
            @if(!empty($linkableProviders))
            <div x-show="showContentSelector" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 @keydown.escape.window="showContentSelector = false">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showContentSelector = false"></div>
                    
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ __('dixlase-menu::admin.settings.menu_items.select_content') }}
                            </h3>
                        </div>
                        
                        <div class="p-6 overflow-y-auto max-h-[60vh]">
                            @foreach($linkableProviders as $provider)
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                                    @if($provider['icon'])
                                    <i class="{{ $provider['icon'] }} mr-2"></i>
                                    @endif
                                    {{ $provider['label'] }}
                                </h4>
                                <div class="space-y-2">
                                    @foreach($provider['items'] as $item)
                                    <button type="button"
                                            @click="selectContent('{{ $item['id'] }}', '{{ addslashes($item['title']) }}', '{{ $item['url'] }}', '{{ $item['type'] }}', '{{ $provider['key'] }}')"
                                            class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                        <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ $item['title'] }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $item['url'] }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="button"
                                    @click="showContentSelector = false"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-md transition-colors">
                                {{ __('common.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </section>

    </form>
@endsection

@section('save')
    <x-admin.save-button
        id_confirmation="confirmMenuSettingsModal"
        :label="__('common.save')"
        :title="__('dixlase-menu::admin.settings.confirm.title')"
        :message="__('dixlase-menu::admin.settings.confirm.message')"
        :confirm_label="__('common.save')"
        :cancel_label="__('common.cancel')"
        form="menu-settings-form"
    />
@endsection

@push('scripts')
<!-- SortableJS for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function menuItems() {
    return {
        items: @json(old('menu_items', $settings['menu_items'] ?? [])),
        showContentSelector: false,
        currentItemIndex: null,
        currentChildIndex: null,
        defaultTarget: '{{ $settings['default_target'] ?? '_self' }}',
        sortableInstance: null,
        
        init() {
            // 親メニューのドラッグ＆ドロップ初期化
            this.$nextTick(() => {
                this.initSortable();
            });
        },
        
        initSortable() {
            const menuList = document.getElementById('menu-list');
            if (menuList && typeof Sortable !== 'undefined') {
                this.sortableInstance = new Sortable(menuList, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: (evt) => {
                        // 配列の順序を更新
                        const item = this.items.splice(evt.oldIndex, 1)[0];
                        this.items.splice(evt.newIndex, 0, item);
                    }
                });
                
                // 子メニューのドラッグ＆ドロップ初期化
                this.initChildSortables();
            }
        },
        
        initChildSortables() {
            // 既存の子メニューリストにSortableを適用
            this.$nextTick(() => {
                document.querySelectorAll('.children-list').forEach((childList) => {
                    if (!childList._sortable) {
                        const parentIndex = parseInt(childList.dataset.parentIndex);
                        childList._sortable = new Sortable(childList, {
                            handle: '.child-drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: (evt) => {
                                // 子配列の順序を更新
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
            return 'menu_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },
        
        addItem() {
            this.items.push({
                id: this.generateId(),
                label: '',
                title_en: '',
                title_ja: '',
                url: '',
                target: this.defaultTarget,
                source_type: 'custom_url',
                source_id: null,
                source_provider: null,
                children: []
            });
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
                title_en: '',
                title_ja: '',
                url: '',
                target: this.defaultTarget,
                source_type: 'custom_url',
                source_id: null,
                source_provider: null
            });
            // 新しい子メニューリストにSortableを適用
            this.initChildSortables();
        },
        
        removeChildItem(parentIndex, childIndex) {
            this.items[parentIndex].children.splice(childIndex, 1);
        },
        
        openContentSelector(parentIndex, childIndex) {
            this.currentItemIndex = parentIndex;
            this.currentChildIndex = childIndex;
            this.showContentSelector = true;
        },
        
        selectContent(id, title, url, type, provider) {
            if (this.currentItemIndex !== null) {
                let target;
                if (this.currentChildIndex !== null) {
                    // 子メニューに設定
                    target = this.items[this.currentItemIndex].children[this.currentChildIndex];
                } else {
                    // 親メニューに設定
                    target = this.items[this.currentItemIndex];
                }
                
                if (target) {
                    target.label = title;
                    target.url = url;
                    target.source_type = type;
                    target.source_id = id;
                    target.source_provider = provider;
                }
            }
            this.showContentSelector = false;
            this.currentItemIndex = null;
            this.currentChildIndex = null;
        }
    }
}
</script>
@endpush
