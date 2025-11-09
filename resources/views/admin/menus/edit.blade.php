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
            <a href="{{ route('admin.dixlase-menu::admin.menus.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
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
            <a href="{{ route('admin.dixlase-menu::admin.menus.delete', $menu->id) }}"
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
            <form id="menu-form" action="{{ route('admin.dixlase-menu::admin.menus.update', $menu->id) }}" method="POST">
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
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- ヘッダー -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('dixlase-menu::admin.menus.edit.menu_items') }}
                        </h2>
                        <a href="{{ route('admin.dixlase-menu::admin.menus.items.create', $menu->id) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ __('dixlase-menu::admin.menus.edit.add_item') }}
                        </a>
                    </div>
                </div>

                <!-- メニューアイテム一覧 -->
                <div class="p-6">
                    @if($menuItems->isEmpty())
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
                                <a href="{{ route('admin.dixlase-menu::admin.menus.items.create', $menu->id) }}"
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('dixlase-menu::admin.menus.edit.add_first_item') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div id="menu-items-container" class="space-y-2">
                            @foreach($menuItems as $item)
                                @include('dixlase-menu::admin.menus.partials.menu-item', ['item' => $item])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/dixlase-menu/css/menu-editor.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('plugins/dixlase-menu/js/menu-form.js') }}"></script>
@if(!$menuItems->isEmpty())
<script src="{{ asset('plugins/dixlase-menu/js/menu-editor.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    new MenuEditor({
        menuId: {{ $menu->id }},
        container: document.getElementById('menu-items-container'),
        maxDepth: {{ $maxDepth }},
        updateOrderUrl: '{{ route("admin.dixlase-menu::admin.menus.items.order", $menu->id) }}',
        moveItemUrl: '{{ route("admin.dixlase-menu::admin.menu-items.move", ":id") }}'
    });
});
</script>
@endif
@endpush
