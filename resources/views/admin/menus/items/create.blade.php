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

@extends('layouts.admin')

@section('title', __('dixlase-menus::admin.menu_items.create.heading'))

@section('content')
<div class="mx-auto">
    <!-- ヘッダー -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
            <a href="{{ route('dixlase-menus::admin.menus.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                {{ __('dixlase-menus::admin.menus.index.heading') }}
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <a href="{{ route('dixlase-menus::admin.menus.edit', $menu->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                {{ $menu->name }}
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span>{{ __('dixlase-menus::admin.menu_items.create.heading') }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('dixlase-menus::admin.menu_items.create.heading') }}
        </h1>
        @if($parentItem)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('dixlase-menus::admin.menu_items.create.parent_item') }}: <strong>{{ $parentItem->title }}</strong>
            </p>
        @endif
    </div>

    <!-- フォーム -->
    <form id="menu-item-form"
          action="{{ route('dixlase-menus::admin.menus.items.store', $menu->id) }}"
          method="POST"
          x-data="menuItemSource"
          data-source-type="{{ old('source_type', 'custom_url') }}"
          data-url="{{ old('url') }}">
        @csrf
        @if($parentItem)
            <input type="hidden" name="parent_id" value="{{ $parentItem->id }}">
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- 基本情報 -->
            <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menus::admin.menu_items.create.basic_info') }}
                </h2>

                <fieldset>
                    <legend class="sr-only">{{ __('dixlase-menus::admin.menu_items.create.basic_info') }}</legend>

                    <div class="space-y-4">
                        <!-- タイトル -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.title') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('title') border-red-500 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menu_items.create.title_help') }}
                            </p>
                        </div>
                    </div>
                </fieldset>
            </section>

            <!-- リンク設定 -->
            <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menus::admin.menu_items.create.link_settings') }}
                </h2>

                <fieldset>
                    <legend class="sr-only">{{ __('dixlase-menus::admin.menu_items.create.link_settings') }}</legend>

                    <div class="space-y-4">
                        <!-- リンクソースタイプ -->
                        <div>
                            <label for="source_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.source_type') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <select id="source_type"
                                    name="source_type"
                                    x-model="sourceType"
                                    @change="updateSource()"
                                    required
                                    class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('source_type') border-red-500 @enderror">
                                @foreach($linkSources as $key => $label)
                                    <option value="{{ $key }}" {{ old('source_type', 'custom_url') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- カスタムURL -->
                        <div x-show="sourceType === 'custom_url'" x-cloak>
                            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.url') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="url"
                                   id="url"
                                   name="url"
                                   x-model="url"
                                   value="{{ old('url') }}"
                                   :required="sourceType === 'custom_url'"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('url') border-red-500 @enderror">
                            @error('url')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menu_items.create.url_help') }}
                            </p>
                        </div>

                        <!-- その他のソース（将来の拡張用） -->
                        <div x-show="sourceType !== 'custom_url'" x-cloak>
                            <label for="source_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.source_id') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   id="source_id"
                                   name="source_id"
                                   x-model="sourceId"
                                   value="{{ old('source_id') }}"
                                   :required="sourceType !== 'custom_url'"
                                   min="1"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('source_id') border-red-500 @enderror">
                            @error('source_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menu_items.create.source_id_help') }}
                            </p>
                        </div>

                        <!-- リンクターゲット -->
                        <div>
                            <label for="target" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.target') }}
                            </label>
                            <select id="target"
                                    name="target"
                                    class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('target') border-red-500 @enderror">
                                <option value="">{{ __('dixlase-menus::admin.menu_items.create.target_default') }}</option>
                                <option value="_self" {{ old('target') == '_self' ? 'selected' : '' }}>{{ __('dixlase-menus::admin.settings.basic.target_self') }}</option>
                                <option value="_blank" {{ old('target') == '_blank' ? 'selected' : '' }}>{{ __('dixlase-menus::admin.settings.basic.target_blank') }}</option>
                                <option value="_parent" {{ old('target') == '_parent' ? 'selected' : '' }}>{{ __('dixlase-menus::admin.settings.basic.target_parent') }}</option>
                                <option value="_top" {{ old('target') == '_top' ? 'selected' : '' }}>{{ __('dixlase-menus::admin.settings.basic.target_top') }}</option>
                            </select>
                            @error('target')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>
            </section>

            <!-- 表示設定 -->
            <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menus::admin.menu_items.create.display_settings') }}
                </h2>

                <fieldset>
                    <legend class="sr-only">{{ __('dixlase-menus::admin.menu_items.create.display_settings') }}</legend>

                    <div class="space-y-4">
                        <!-- CSSクラス -->
                        <div>
                            <label for="css_class" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.css_class') }}
                            </label>
                            <input type="text"
                                   id="css_class"
                                   name="css_class"
                                   value="{{ old('css_class') }}"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('css_class') border-red-500 @enderror">
                            @error('css_class')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menu_items.create.css_class_help') }}
                            </p>
                        </div>

                        <!-- アイコンクラス -->
                        <div>
                            <label for="icon_class" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.icon_class') }}
                            </label>
                            <input type="text"
                                   id="icon_class"
                                   name="icon_class"
                                   value="{{ old('icon_class') }}"
                                   placeholder="fas fa-home"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('icon_class') border-red-500 @enderror">
                            @error('icon_class')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menu_items.create.icon_class_help') }}
                            </p>
                        </div>

                        <!-- 表示順 -->
                        <div>
                            <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menu_items.create.display_order') }}
                            </label>
                            <input type="number"
                                   id="display_order"
                                   name="display_order"
                                   value="{{ old('display_order', 0) }}"
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
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                            </div>
                            <div class="ml-3">
                                <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('dixlase-menus::admin.menu_items.create.is_active') }}
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('dixlase-menus::admin.menu_items.create.is_active_help') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </section>

            <!-- アクションボタン -->
            <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                <x-admin.save-button
                    form="menu-item-form"
                    :label="__('common.create')"
                    :confirm_label="__('common.create')"
                    :back_url="route('dixlase-menus::admin.menus.edit', $menu->id)"
                />
            </div>
        </div>
    </form>
</div>
@endsection

