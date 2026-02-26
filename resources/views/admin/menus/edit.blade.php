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
     data-placement-descriptions='@json($placementTypeDescriptions)'>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 左カラム: メニュー基本情報 -->
        <div class="lg:col-span-1">
            <form id="menu-form" x-ref="menuForm" action="{{ route('dixlase-menus::admin.menus.update', $menu->id) }}" method="POST" @submit.prevent>
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- 基本情報 -->
                    <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('dixlase-menus::admin.menus.edit.basic_info') }}
                        </h2>

                        <fieldset>
                            <legend class="sr-only">{{ __('dixlase-menus::admin.menus.edit.basic_info') }}</legend>

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
                        </fieldset>
                    </section>

                    <!-- 表示設定 -->
                    <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('dixlase-menus::admin.menus.edit.display_settings') }}
                        </h2>

                        <fieldset>
                            <legend class="sr-only">{{ __('dixlase-menus::admin.menus.edit.display_settings') }}</legend>

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

                                <!-- 表示位置 -->
                                <div x-effect="$el.querySelector('select').disabled = !isAuto"
                                     :class="{ 'opacity-50': !isAuto }">
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
                                <div :class="{ 'opacity-50': !isAuto }">
                                    <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menus::admin.menus.edit.display_order') }}
                                    </label>
                                    <input type="number"
                                           id="display_order"
                                           name="display_order"
                                           value="{{ old('display_order', $menu->display_order) }}"
                                           min="0"
                                           x-bind:disabled="!isAuto"
                                           class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('display_order') border-red-500 @enderror disabled:cursor-not-allowed">
                                    @error('display_order')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>


                            </div>
                        </fieldset>
                    </section>

                    <!-- 配置コード -->
                    <section class="p-6" :class="{ 'opacity-50 pointer-events-none': isAuto }">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('dixlase-menus::admin.menus.edit.placement_code') }}
                        </h2>

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
                    </section>
                </div>
            </form>
        </div>

        <!-- 右カラム: メニューアイテム管理 -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- ヘッダー -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('dixlase-menus::admin.menus.edit.menu_items') }}
                        </h2>
                        <div class="flex items-center gap-2">
                            <x-form-button
                                type="button"
                                variant="primary"
                                icon="fas fa-plus"
                                :label="__('dixlase-menus::admin.menus.edit.add_item')"
                                xClick="addItem()"
                            />
                        </div>
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
                                    xClick="addItem()"
                                />
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
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>

                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- タイトル -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menus::admin.menu_items.create.title') }}
                                            </label>
                                            <input type="text"
                                                   x-model="item.label"
                                                   placeholder="{{ __('dixlase-menus::admin.settings.menu_items.label_placeholder') }}"
                                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>

                                        <!-- URL -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ __('dixlase-menus::admin.menu_items.create.url') }}
                                            </label>
                                            <input type="text"
                                                   x-model="item.url"
                                                   placeholder="{{ __('dixlase-menus::admin.settings.menu_items.url_placeholder') }}"
                                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>

                                        <!-- ターゲット -->
                                        <div>
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
                                    <div class="flex items-center gap-1">
                                        <!-- 子メニュー追加ボタン -->
                                        <button type="button"
                                                @click="addChildItem(index)"
                                                x-show="item.depth < maxDepth - 1"
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
    </div>
</div>

<!-- 削除ボタン -->
<div class="mb-6">
    <div class="flex items-center justify-end">
        <x-form-button
            type="link"
            variant="danger"
            icon="fas fa-trash"
            :label="__('common.delete')"
            :href="route('dixlase-menus::admin.menus.delete', $menu->id)"
        />
    </div>
</div>

@endsection

@section('save')
    <x-admin.save-button
        form="menu-form"
        :title="__('dixlase-menus::admin.menus.edit.confirm_title')"
        :message="__('dixlase-menus::admin.menus.edit.confirm_message')"
        :back_url="route('dixlase-menus::admin.menus.index')"
    />
@endsection
