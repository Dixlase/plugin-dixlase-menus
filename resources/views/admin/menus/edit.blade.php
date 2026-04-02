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

@section('title', __('dixlase-menus::admin.menus.edit.heading'))

@section('content')
<div class="max-w-7xl mx-auto"
     x-data="menuEditor"
     data-placement-type="{{ old('placement_type', $menu->placement_type?->value ?? 'manual') }}"
     data-menu-id="{{ $menu->id }}"
     data-max-depth="{{ $maxDepth }}"
     data-sync-url="{{ route('dixlase-menus::admin.menus.items.sync', $menu->id) }}"
     data-items='@json($menuItems)'
     data-error-message="{{ __('dixlase-menus::admin.messages.menu_items_save_failed') }}"
     data-placement-descriptions='@json($placementTypeDescriptions)'
     data-link-sources-url="{{ $linkSourcesUrl }}">

    <!-- メインコンテンツ: メニューアイテム管理 -->
    <div class="space-y-6">
        <!-- メニューアイテム一覧 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- ヘッダー -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('dixlase-menus::admin.menus.edit.menu_items') }}
                    </h2>
                    <x-form-button
                        type="button"
                        variant="primary"
                        icon="fas fa-plus"
                        size="sm"
                        :label="__('dixlase-menus::admin.menus.edit.add_item')"
                        xClick="openAddItemModal()"
                    />
                </div>
            </div>

            <!-- メニューアイテム一覧 -->
            <div class="p-6">
                <!-- 空の状態 -->
                <template x-if="items.length === 0">
                    <div class="text-center py-12">
                        <i class="fas fa-bars text-4xl text-gray-400"></i>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('dixlase-menus::admin.menus.edit.no_items') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('dixlase-menus::admin.menus.edit.no_items_description') }}
                        </p>
                        <div class="mt-6">
                            <x-form-button
                                type="button"
                                variant="primary"
                                icon="fas fa-plus"
                                :label="__('dixlase-menus::admin.menus.edit.add_first_item')"
                                xClick="openAddItemModal()"
                            />
                        </div>
                    </div>
                </template>

                <!-- メニューアイテムリスト -->
                <div id="menu-items-list" class="space-y-3" x-show="items.length > 0">
                    <template x-for="(item, index) in items" :key="item.id || index">
                        <div class="menu-item bg-gray-50 dark:bg-gray-700 rounded-md" :data-index="index">
                            <!-- 親メニュー -->
                            <div class="flex items-end gap-2 p-4">
                                <!-- ドラッグハンドル -->
                                <div class="drag-handle cursor-move p-1 mb-1.25 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>

                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- タイトル -->
                                    <div :class="item.source_type === 'menu_group' ? 'md:col-span-3' : ''">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('dixlase-menus::admin.menu_items.create.title') }}
                                            <span x-show="item.source_type === 'menu_group'"
                                                  class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                <i class="fas fa-layer-group mr-1 text-[10px]"></i>{{ __('dixlase-menus::admin.menus.edit.menu_group_badge') }}
                                            </span>
                                        </label>
                                        <input type="text"
                                               x-model="item.label"
                                               placeholder="{{ __('dixlase-menus::admin.settings.menu_items.label_placeholder') }}"
                                               class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- URL -->
                                    <div x-show="item.source_type !== 'menu_group'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('dixlase-menus::admin.menu_items.create.url') }}
                                        </label>
                                        <input type="text"
                                               x-model="item.url"
                                               placeholder="{{ __('dixlase-menus::admin.settings.menu_items.url_placeholder') }}"
                                               class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- ターゲット -->
                                    <div x-show="item.source_type !== 'menu_group'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('dixlase-menus::admin.menu_items.create.target') }}
                                        </label>
                                        <select x-model="item.target"
                                                class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="_self">{{ __('dixlase-menus::admin.settings.basic.target_self') }}</option>
                                            <option value="_blank">{{ __('dixlase-menus::admin.settings.basic.target_blank') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- アクションボタン -->
                                <div class="flex items-center gap-1 mb-0.5">
                                    <!-- 子メニュー追加ボタン -->
                                    <button type="button"
                                            @click="openAddItemModal(index)"
                                            x-show="item.source_type === 'menu_group' && item.depth < maxDepth - 1"
                                            class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="{{ __('dixlase-menus::admin.menus.edit.add_child') }}">
                                        <i class="fas fa-plus"></i>
                                    </button>

                                    <!-- 削除ボタン -->
                                    <button type="button"
                                            @click="removeItem(index)"
                                            class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <i class="fas fa-times"></i>
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
                                                <i class="fas fa-grip-vertical text-sm"></i>
                                            </div>

                                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <!-- タイトル -->
                                                <div>
                                                    <input type="text"
                                                           x-model="child.label"
                                                           placeholder="{{ __('dixlase-menus::admin.settings.menu_items.label_placeholder') }}"
                                                           class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                </div>

                                                <!-- URL -->
                                                <div>
                                                    <input type="text"
                                                           x-model="child.url"
                                                           placeholder="{{ __('dixlase-menus::admin.settings.menu_items.url_placeholder') }}"
                                                           class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                </div>

                                                <!-- ターゲット -->
                                                <div>
                                                    <select x-model="child.target"
                                                            class="block w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                        <option value="_self">{{ __('dixlase-menus::admin.settings.basic.target_self') }}</option>
                                                        <option value="_blank">{{ __('dixlase-menus::admin.settings.basic.target_blank') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- 削除ボタン -->
                                            <button type="button"
                                                    @click="removeChildItem(index, childIndex)"
                                                    class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                <i class="fas fa-times text-sm"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- ヘルプテキスト -->
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400" x-show="items.length > 0">
                    {{ __('dixlase-menus::admin.settings.menu_items.items_help') }}
                </p>

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

    <!-- 右サイドバートグルボタン -->
    <button type="button"
            @click="toggleRightSidebar()"
            class="flex fixed top-14 right-0 z-50 items-center backdrop-blur-sm dark:bg-gray-900/75 bg-white/75 text-blue-400 dark:text-white px-1.5 py-4 rounded-l-lg shadow-md border border-r-0 border-gray-300 dark:border-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
            :class="{
                'translate-x-0': rightSidebarCollapsed,
                '-translate-x-80': !rightSidebarCollapsed
            }"
            :style="rightSidebarReady ? 'transition: transform 200ms ease-in-out' : ''"
            :aria-label="rightSidebarCollapsed
                ? '{{ __('dixlase-menus::admin.menus.edit.sidebar_open') }}'
                : '{{ __('dixlase-menus::admin.menus.edit.sidebar_close') }}'">
        <i class="fas text-sm" :class="rightSidebarCollapsed ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
    </button>

    <!-- 右サイドバー -->
    <div class="space-y-6 fixed top-12 right-0 bottom-0 w-80 z-50 overflow-y-auto bg-white/75 dark:bg-gray-900/75 backdrop-blur-sm border-l border-gray-200 dark:border-gray-600 shadow-md px-6 py-6"
         :class="{
             'translate-x-80': rightSidebarCollapsed,
             'translate-x-0': !rightSidebarCollapsed
         }"
         :style="rightSidebarReady ? 'transition: transform 300ms ease-in-out' : ''">

        <form id="menu-form" x-ref="menuForm" action="{{ route('dixlase-menus::admin.menus.update', $menu->id) }}" method="POST" @submit.prevent>
            @csrf
            @method('PUT')

            <!-- 基本情報 -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menus::admin.menus.edit.basic_info') }}
                </h2>

                <div class="space-y-4">
                    <!-- メニュー名 -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('dixlase-menus::admin.menus.edit.name') }}
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
                            {{ __('dixlase-menus::admin.menus.edit.slug') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                                id="slug"
                                name="slug"
                                value="{{ old('slug', $menu->slug) }}"
                                required
                                pattern="[a-z0-9_\-]+"
                                class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('slug') border-red-500 @enderror">
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 説明 -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('dixlase-menus::admin.menus.edit.description') }}
                        </label>
                        <textarea id="description"
                                    name="description"
                                    rows="3"
                                    class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description', $menu->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- アクティブ状態 -->
                    <div>
                        <x-form-toggle
                            name="is_active"
                            :checked="old('is_active', $menu->is_active)"
                            :label="__('dixlase-menus::admin.menus.edit.is_active')"
                        />
                    </div>
                </div>
            </div>

            <!-- 表示設定 -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menus::admin.menus.edit.display_settings') }}
                </h2>

                <div class="space-y-4">
                    <!-- 配置方法 -->
                    <div>
                        <x-form-label for="placement_type" :required="true">
                            {{ __('dixlase-menus::admin.menus.edit.placement_type') }}
                        </x-form-label>
                        <x-form-select
                            name="placement_type"
                            id="placement_type"
                            :options="$placementTypeOptions"
                            :value="old('placement_type', $menu->placement_type?->value ?? 'manual')"
                            xModel="placementType"
                        />
                        @error('placement_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="placementDescription"></p>
                    </div>

                    <!-- 自動配置: 表示位置・表示順 -->
                    <template x-if="isAuto">
                        <div class="space-y-4">
                            <!-- 表示位置 -->
                            <div>
                                <x-form-label for="location">
                                    {{ __('dixlase-menus::admin.menus.edit.location') }}
                                </x-form-label>
                                <x-form-select
                                    name="location"
                                    :options="array_merge(['' => __('dixlase-menus::admin.menus.edit.select_location')], $locationOptions)"
                                    :value="old('location', $menu->location)"
                                />
                                @error('location')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 表示順 -->
                            <div>
                                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('dixlase-menus::admin.menus.edit.display_order') }}
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
                        </div>
                    </template>

                    <!-- 手動配置: 配置コード -->
                    <template x-if="!isAuto">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('dixlase-menus::admin.menus.edit.blade_directive') }}
                                </label>
                                <code class="block w-full px-3 py-2 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-mono text-gray-800 dark:text-gray-200">@@menu('{{ $menu->slug }}')</code>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('dixlase-menus::admin.menus.edit.shortcode') }}
                                </label>
                                <code class="block w-full px-3 py-2 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-mono text-gray-800 dark:text-gray-200">[menu slug="{{ $menu->slug }}"]</code>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </form>

        <!-- 削除ボタン -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <x-form-button
                type="button"
                variant="danger"
                :label="__('common.delete')"
                icon="fas fa-trash-alt"
                @click="openModal('delete-menu-modal')"
            />
        </div>
    </div>
    <!-- アイテム追加モーダル -->
    <div id="add-item-modal"
         class="modal"
         x-show="addItemModalOpen"
         x-cloak
         @keydown.escape.window="if (addItemModalOpen) addItemModalOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="modal-overlay bg-white/80 dark:bg-black/50"
             @click="addItemModalOpen = false"></div>
        <div class="modal-container !max-w-xl"
             @click.stop
             style="transition: opacity 300ms ease-out, transform 300ms ease-out;">
            <div class="modal-content !text-left !p-0">
                <!-- モーダルヘッダー -->
                <div class="flex items-center justify-between px-6 pt-6 pb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="addItemParentIndex === null">{{ __('dixlase-menus::admin.menus.edit.add_items') }}</span>
                        <span x-show="addItemParentIndex !== null">{{ __('dixlase-menus::admin.menus.edit.add_child') }}</span>
                    </h2>
                    <button type="button" @click="addItemModalOpen = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- 追加先の親アイテム表示 -->
                <div x-show="addItemParentIndex !== null && items[addItemParentIndex]"
                     class="mx-6 mb-3 px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-md text-sm text-indigo-700 dark:text-indigo-300">
                    <i class="fas fa-level-down-alt mr-1"></i>
                    <span x-text="addItemParentIndex !== null && items[addItemParentIndex] ? items[addItemParentIndex].label : ''"></span>
                    {{ __('dixlase-menus::admin.menus.edit.add_child_hint') }}
                </div>

                <!-- タブナビゲーション -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px overflow-x-auto px-6" aria-label="Tabs">
                        <!-- カスタムURLタブ -->
                        <button type="button"
                                @click="addItemActiveTab = 'custom_url'"
                                :class="addItemActiveTab === 'custom_url'
                                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-link"></i>
                            {{ __('dixlase-menus::admin.menus.edit.custom_url') }}
                        </button>

                        <!-- プロバイダータブ（動的） -->
                        <template x-for="source in linkSources.filter(s => s.hasItems)" :key="'modal-tab-' + source.type">
                            <button type="button"
                                    @click="addItemActiveTab = source.type; if (!sourceItems[source.type]) loadSourceItems(source.type)"
                                    :class="addItemActiveTab === source.type
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                    class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium flex items-center gap-2">
                                <i :class="source.icon"></i>
                                <span x-text="source.label"></span>
                            </button>
                        </template>

                        <!-- メニューグループタブ（ルートレベルのみ） -->
                        <button type="button"
                                x-show="addItemParentIndex === null"
                                @click="addItemActiveTab = 'menu_group'"
                                :class="addItemActiveTab === 'menu_group'
                                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            {{ __('dixlase-menus::admin.menus.edit.menu_group') }}
                        </button>
                    </nav>
                </div>

                <!-- タブコンテンツ -->
                <div class="px-6 py-4">
                    <!-- カスタムURLコンテンツ -->
                    <div x-show="addItemActiveTab === 'custom_url'" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.title') }}
                            </label>
                            <input type="text"
                                   x-model="customUrl.label"
                                   placeholder="{{ __('dixlase-menus::admin.menus.edit.custom_url_label') }}"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.url') }}
                            </label>
                            <input type="text"
                                   x-model="customUrl.url"
                                   placeholder="{{ __('dixlase-menus::admin.menus.edit.custom_url_url') }}"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- カテゴリコンテンツ -->
                    <div x-show="addItemActiveTab === 'menu_group'" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menus.edit.menu_group_label') }}
                            </label>
                            <input type="text"
                                   x-model="menuGroupLabel"
                                   placeholder="{{ __('dixlase-menus::admin.menus.edit.menu_group_label_placeholder') }}"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>{{ __('dixlase-menus::admin.menus.edit.menu_group_hint') }}
                        </p>
                    </div>

                    <!-- プロバイダーコンテンツ（動的） -->
                    <template x-for="source in linkSources.filter(s => s.hasItems)" :key="'modal-content-' + source.type">
                        <div x-show="addItemActiveTab === source.type" class="space-y-3">
                            <!-- 検索 -->
                            <input type="text"
                                   :value="sourceSearch[source.type] || ''"
                                   @input.debounce.300ms="sourceSearch[source.type] = $event.target.value; searchSourceItems(source.type)"
                                   placeholder="{{ __('dixlase-menus::admin.menus.edit.search_items') }}"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                            <!-- 読み込み中 -->
                            <div x-show="loadingSources[source.type]" class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                {{ __('dixlase-menus::admin.menus.edit.loading_items') }}
                            </div>

                            <!-- アイテムリスト -->
                            <div x-show="!loadingSources[source.type] && sourceItems[source.type]" class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-md">
                                <template x-if="sourceItems[source.type] && sourceItems[source.type].length === 0">
                                    <div class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('dixlase-menus::admin.menus.edit.no_source_items') }}
                                    </div>
                                </template>
                                <template x-for="sourceItem in (sourceItems[source.type] || [])" :key="sourceItem.id">
                                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                        <input type="checkbox"
                                               :checked="selectedItems[source.type] && selectedItems[source.type].has(sourceItem.id)"
                                               @change="toggleSourceItem(source.type, sourceItem.id)"
                                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="sourceItem.title"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- 注意書き（プロバイダータブ選択時のみ表示） -->
                    <p x-show="addItemActiveTab !== 'custom_url' && addItemActiveTab !== 'menu_group'"
                       class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        <i class="fas fa-info-circle mr-1"></i>{{ __('dixlase-menus::admin.menus.edit.published_only_hint') }}
                    </p>
                </div>
            </div>

            <!-- モーダルフッター -->
            <div class="modal-actions">
                <x-form-button
                    type="button"
                    variant="secondary"
                    icon="fas fa-times"
                    @click="addItemModalOpen = false"
                    class="mx-2"
                >{{ __('common.cancel') }}</x-form-button>

                <!-- カスタムURL追加ボタン -->
                <x-form-button
                    type="button"
                    variant="primary"
                    icon="fas fa-plus"
                    xShow="addItemActiveTab === 'custom_url'"
                    xClick="addCustomUrl()"
                    xBind:disabled="!customUrl.label || !customUrl.url"
                    class="mx-2"
                >{{ __('dixlase-menus::admin.menus.edit.add_to_menu') }}</x-form-button>

                <!-- カテゴリ追加ボタン -->
                <x-form-button
                    type="button"
                    variant="primary"
                    icon="fas fa-plus"
                    xShow="addItemActiveTab === 'menu_group'"
                    xClick="addMenuGroup()"
                    xBind:disabled="!menuGroupLabel"
                    class="mx-2"
                >{{ __('dixlase-menus::admin.menus.edit.add_to_menu') }}</x-form-button>

                <!-- プロバイダー追加ボタン -->
                <template x-for="source in linkSources.filter(s => s.hasItems)" :key="'modal-btn-' + source.type">
                    <div x-show="addItemActiveTab === source.type" class="inline-block">
                        <x-form-button
                            type="button"
                            variant="primary"
                            icon="fas fa-plus"
                            xClick="addSelectedItems(source.type)"
                            xBind:disabled="!selectedItems[source.type] || selectedItems[source.type].size === 0"
                            class="mx-2"
                        >{{ __('dixlase-menus::admin.menus.edit.add_to_menu') }}</x-form-button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- 削除フォーム・モーダル（サイドバー外に配置して画面中央に表示） -->
<form id="delete-menu-form" action="{{ route('dixlase-menus::admin.menus.destroy', $menu->id) }}" method="POST">
    @csrf
    @method('DELETE')
</form>
<x-ui-modal
    id="delete-menu-modal"
    :title="__('dixlase-menus::admin.menus.delete_confirm_title')"
    :message="__('dixlase-menus::admin.menus.delete_confirm_message')"
    :confirm_label="__('common.delete')"
    icon_type="danger"
    confirm_color="red"
    form="delete-menu-form"
/>
@endsection

@section('save')
    <x-admin.save-button
        form="menu-form"
        :title="__('dixlase-menus::admin.menus.edit.confirm_title')"
        :message="__('dixlase-menus::admin.menus.edit.confirm_message')"
        :back_url="route('dixlase-menus::admin.menus.index')"
    />
@endsection
