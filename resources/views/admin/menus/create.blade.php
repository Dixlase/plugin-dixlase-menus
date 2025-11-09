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

@section('title', __('dixlase-menu::admin.menus.create.heading'))

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- ヘッダー -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
            <a href="{{ route('admin.dixlase-menu::admin.menus.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                {{ __('dixlase-menu::admin.menus.index.heading') }}
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span>{{ __('dixlase-menu::admin.menus.create.heading') }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('dixlase-menu::admin.menus.create.heading') }}
        </h1>
    </div>

    <!-- フォーム -->
    <form id="menu-form" action="{{ route('admin.dixlase-menu::admin.menus.store') }}" method="POST">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- 基本情報 -->
            <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menu::admin.menus.create.basic_info') }}
                </h2>

                <fieldset>
                    <legend class="sr-only">{{ __('dixlase-menu::admin.menus.create.basic_info') }}</legend>

                    <div class="space-y-4">
                        <!-- メニュー名 -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menu::admin.menus.create.name') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menu::admin.menus.create.name_help') }}
                            </p>
                        </div>

                        <!-- スラッグ -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menu::admin.menus.create.slug') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug') }}"
                                   required
                                   pattern="[a-z0-9-_]+"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('slug') border-red-500 @enderror">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menu::admin.menus.create.slug_help') }}
                            </p>
                        </div>

                        <!-- 説明 -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menu::admin.menus.create.description') }}
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menu::admin.menus.create.description_help') }}
                            </p>
                        </div>
                    </div>
                </fieldset>
            </section>

            <!-- 表示設定 -->
            <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menu::admin.menus.create.display_settings') }}
                </h2>

                <fieldset>
                    <legend class="sr-only">{{ __('dixlase-menu::admin.menus.create.display_settings') }}</legend>

                    <div class="space-y-4">
                        <!-- 表示位置 -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menu::admin.menus.create.location') }}
                            </label>
                            <select id="location"
                                    name="location"
                                    class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('location') border-red-500 @enderror">
                                <option value="">{{ __('dixlase-menu::admin.menus.create.select_location') }}</option>
                                @foreach($availableLocations as $loc)
                                    <option value="{{ $loc['key'] }}" {{ old('location') == $loc['key'] ? 'selected' : '' }}>
                                        {{ $loc['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menu::admin.menus.create.location_help') }}
                            </p>
                        </div>

                        <!-- 表示順 -->
                        <div>
                            <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menu::admin.menus.create.display_order') }}
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
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menu::admin.menus.create.display_order_help') }}
                            </p>
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
                                    {{ __('dixlase-menu::admin.menus.create.is_active') }}
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('dixlase-menu::admin.menus.create.is_active_help') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </section>

            <!-- アクションボタン -->
            <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dixlase-menu::admin.menus.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ __('common.back') }}
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('common.create') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/dixlase-menu/js/menu-form.js') }}"></script>
@endpush
