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

@section('title', __('dixlase-menus::admin.menus.create.heading'))

@section('content')
<div class="mx-auto">

    <!-- フォーム -->
    <form id="menu-form" action="{{ route('dixlase-menus::admin.menus.store') }}" method="POST">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- 基本情報 -->
            <section class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menus::admin.menus.create.basic_info') }}
                </h2>

                <fieldset>
                    <legend class="sr-only">{{ __('dixlase-menus::admin.menus.create.basic_info') }}</legend>

                    <div class="space-y-4">
                        <!-- メニュー名 -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menus.create.name') }}
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
                                {{ __('dixlase-menus::admin.menus.create.name_help') }}
                            </p>
                        </div>

                        <!-- スラッグ -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menus.create.slug') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug') }}"
                                   required
                                   pattern="[a-z0-9_\-]+"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('slug') border-red-500 @enderror">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menus.create.slug_help') }}
                            </p>
                        </div>

                        <!-- 説明 -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menus.create.description') }}
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menus.create.description_help') }}
                            </p>
                        </div>
                    </div>
                </fieldset>
            </section>

            <!-- 表示設定 -->
            <section class="p-6 border-b border-gray-200 dark:border-gray-700"
                     x-data="menuPlacement"
                     data-placement-type="{{ old('placement_type', 'manual') }}">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('dixlase-menus::admin.menus.create.display_settings') }}
                </h2>

                <fieldset>
                    <legend class="sr-only">{{ __('dixlase-menus::admin.menus.create.display_settings') }}</legend>

                    <div class="space-y-4">
                        <!-- 配置方法 -->
                        <div>
                            <x-form-label for="placement_type" :required="true">
                                {{ __('dixlase-menus::admin.menus.create.placement_type') }}
                            </x-form-label>
                            <x-form-radio-card-group
                                name="placement_type"
                                :options="$placementTypeOptions"
                                :value="old('placement_type', 'manual')"
                                xModel="placementType"
                                :columns="2"
                            />
                            @error('placement_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 手動配置のヒント -->
                        <div x-show="!isAuto" x-transition>
                            <p class="text-sm text-amber-600 dark:text-amber-400">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ __('dixlase-menus::admin.menus.create.manual_create_hint') }}
                            </p>
                        </div>

                        <!-- 表示位置 -->
                        <div x-effect="$el.querySelector('select').disabled = !isAuto"
                             :class="{ 'opacity-50': !isAuto }">
                            <x-form-label for="location">
                                {{ __('dixlase-menus::admin.menus.create.location') }}
                            </x-form-label>
                            <x-form-select
                                name="location"
                                :options="array_merge(['' => __('dixlase-menus::admin.menus.create.select_location')], $locationOptions)"
                                :value="old('location')"
                            />
                            @error('location')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menus.create.location_help') }}
                            </p>
                        </div>

                        <!-- 表示順 -->
                        <div :class="{ 'opacity-50': !isAuto }">
                            <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('dixlase-menus::admin.menus.create.display_order') }}
                            </label>
                            <input type="number"
                                   id="display_order"
                                   name="display_order"
                                   value="{{ old('display_order', 0) }}"
                                   min="0"
                                   x-bind:disabled="!isAuto"
                                   class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('display_order') border-red-500 @enderror disabled:cursor-not-allowed">
                            @error('display_order')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('dixlase-menus::admin.menus.create.display_order_help') }}
                            </p>
                        </div>

                        <!-- アクティブ状態 -->
                        <x-form-toggle
                            name="is_active"
                            :checked="old('is_active', true)"
                            :label="__('dixlase-menus::admin.menus.create.is_active')"
                        />
                    </div>
                </fieldset>
            </section>

        </div>
    </form>
</div>
@endsection

@section('save')
    <x-admin.save-button
        form="menu-form"
        :label="__('common.create')"
        :title="__('dixlase-menus::admin.menus.create.confirm_title')"
        :message="__('dixlase-menus::admin.menus.create.confirm_message')"
        :confirm_label="__('common.create')"
        :back_url="route('dixlase-menus::admin.menus.index')"
    />
@endsection

