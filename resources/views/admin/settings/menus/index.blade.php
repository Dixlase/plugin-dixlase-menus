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
    <form id="menu-settings-form" action="{{ route('admin.dixlase-menu::admin.settings.menus.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- 基本設定 -->
        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-4">{{ __('dixlase-menu::admin.settings.basic.title') }}</h2>
            
            <fieldset>
                <legend>{{ __('dixlase-menu::admin.settings.basic.menu_structure') }}</legend>
                
                <div class="grid grid-cols-1 gap-6">
                    @include('components::form.number', [
                        'name' => 'max_menu_depth',
                        'label' => __('dixlase-menu::admin.settings.basic.max_menu_depth'),
                        'value' => old('max_menu_depth', $settings['max_menu_depth'] ?? 3),
                        'required' => true,
                        'min' => 1,
                        'max' => 10,
                        'help' => __('dixlase-menu::admin.settings.basic.max_menu_depth_help')
                    ])

                    @include('components::form.select', [
                        'name' => 'default_target',
                        'label' => __('dixlase-menu::admin.settings.basic.default_target'),
                        'options' => [
                            '_self' => __('dixlase-menu::admin.settings.basic.target_self'),
                            '_blank' => __('dixlase-menu::admin.settings.basic.target_blank'),
                            '_parent' => __('dixlase-menu::admin.settings.basic.target_parent'),
                            '_top' => __('dixlase-menu::admin.settings.basic.target_top'),
                        ],
                        'value' => old('default_target', $settings['default_target'] ?? '_self'),
                        'required' => true,
                        'help' => __('dixlase-menu::admin.settings.basic.default_target_help')
                    ])
                </div>
            </fieldset>
        </section>

        <!-- キャッシュ設定 -->
        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-4">{{ __('dixlase-menu::admin.settings.cache.title') }}</h2>
            
            <fieldset>
                <legend>{{ __('dixlase-menu::admin.settings.cache.menu_cache') }}</legend>
                
                <div class="grid grid-cols-1 gap-6">
                    @include('components::form.checkbox', [
                        'name' => 'enable_menu_cache',
                        'label' => __('dixlase-menu::admin.settings.cache.enable_menu_cache'),
                        'checked' => old('enable_menu_cache', $settings['enable_menu_cache'] ?? true),
                        'help' => __('dixlase-menu::admin.settings.cache.enable_menu_cache_help')
                    ])

                    @include('components::form.number', [
                        'name' => 'cache_duration',
                        'label' => __('dixlase-menu::admin.settings.cache.cache_duration'),
                        'value' => old('cache_duration', $settings['cache_duration'] ?? 3600),
                        'required' => true,
                        'min' => 60,
                        'max' => 86400,
                        'help' => __('dixlase-menu::admin.settings.cache.cache_duration_help')
                    ])
                </div>
            </fieldset>

            <!-- キャッシュクリアボタン -->
            <div class="mt-6">
                <button type="button" 
                        onclick="clearMenuCache()"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ __('dixlase-menu::admin.settings.cache.clear_cache') }}
                </button>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('dixlase-menu::admin.settings.cache.clear_cache_help') }}
                </p>
            </div>
        </section>

        <!-- メニュー位置設定 -->
        <section class="mb-8" x-data="menuLocations()">
            <h2 class="text-xl font-semibold mb-4">{{ __('dixlase-menu::admin.settings.locations.title') }}</h2>
            
            <fieldset>
                <legend>{{ __('dixlase-menu::admin.settings.locations.available_locations') }}</legend>
                
                <div class="space-y-4">
                    <template x-for="(location, index) in locations" :key="index">
                        <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menu::admin.settings.locations.location_key') }}
                                    </label>
                                    <input type="text"
                                           :name="'available_locations[' + index + '][key]'"
                                           x-model="location.key"
                                           class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('dixlase-menu::admin.settings.locations.location_label') }}
                                    </label>
                                    <input type="text"
                                           :name="'available_locations[' + index + '][label]'"
                                           x-model="location.label"
                                           class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           required>
                                </div>
                            </div>
                            <button type="button"
                                    @click="removeLocation(index)"
                                    class="mt-6 p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <button type="button"
                            @click="addLocation()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('dixlase-menu::admin.settings.locations.add_location') }}
                    </button>
                </div>

                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('dixlase-menu::admin.settings.locations.locations_help') }}
                </p>
            </fieldset>
        </section>

        <!-- 保存ボタン -->
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition-colors duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ __('common.save') }}
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
function menuLocations() {
    return {
        locations: @json(old('available_locations', $settings['available_locations'] ?? [
            ['key' => 'header', 'label' => 'Header Menu'],
            ['key' => 'footer', 'label' => 'Footer Menu'],
        ])),
        
        addLocation() {
            this.locations.push({
                key: '',
                label: ''
            });
        },
        
        removeLocation(index) {
            this.locations.splice(index, 1);
        }
    }
}

function clearMenuCache() {
    if (!confirm('{{ __("dixlase-menu::admin.settings.cache.clear_cache_confirm") }}')) {
        return;
    }
    
    fetch('{{ route("admin.dixlase-menu::admin.settings.menus.cache.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        alert('{{ __("dixlase-menu::admin.messages.cache_cleared") }}');
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("common.error") }}');
    });
}
</script>
@endpush
