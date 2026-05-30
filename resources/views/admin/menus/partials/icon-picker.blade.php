{{--
 * Icon picker modal (FA Free)
 *
 * Hand-rolled so we don't depend on the host's <x-ui-modal>, which always
 * renders a header icon circle that doesn't fit a content-driven picker.
 * Pattern-consistent with the add-item-modal in menus/edit.blade.php.
 *
 * State lives on the surrounding menuEditor Alpine component:
 *   iconPickerOpen, iconPickerTarget, iconPickerSearch,
 *   iconPickerStyle, iconPickerCategory, iconPickerLoading,
 *   filteredIcons, filteredIconsTotal, iconCategories,
 *   selectIcon(name, prefix), clearIcon(), closeIconPicker()
--}}
<div id="icon-picker-modal"
     class="modal"
     x-show="iconPickerOpen"
     x-cloak
     @keydown.escape.window="if (iconPickerOpen) closeIconPicker()"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;">
    <div class="modal-overlay bg-white/80 dark:bg-black/50"
         @click="closeIconPicker()"></div>
    <div class="modal-container !max-w-3xl"
         @click.stop
         style="transition: opacity 300ms ease-out, transform 300ms ease-out;">
        <div class="modal-content !text-left !p-0">
            {{-- ヘッダ --}}
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('dixlase-menus::admin.menus.edit.icon_picker.title') }}
                </h2>
                <button type="button" @click="closeIconPicker()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- 検索 + フィルタ --}}
            <div class="px-6 py-4 space-y-3">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 dark:text-gray-500">
                        <i class="fas fa-search text-sm"></i>
                    </span>
                    <input type="text"
                           x-model.debounce.150ms="iconPickerSearch"
                           placeholder="{{ __('dixlase-menus::admin.menus.edit.icon_picker.search_placeholder') }}"
                           class="block w-full pl-9 pr-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- スタイルタブ --}}
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <template x-for="(opt, i) in [
                            { v: 'all', l: '{{ __('dixlase-menus::admin.menus.edit.icon_picker.style_all') }}' },
                            { v: 'fas', l: '{{ __('dixlase-menus::admin.menus.edit.icon_picker.style_solid') }}' },
                            { v: 'far', l: '{{ __('dixlase-menus::admin.menus.edit.icon_picker.style_regular') }}' },
                            { v: 'fab', l: '{{ __('dixlase-menus::admin.menus.edit.icon_picker.style_brands') }}' }
                        ]" :key="opt.v">
                            <button type="button"
                                    @click="iconPickerStyle = opt.v"
                                    :class="[
                                        iconPickerStyle === opt.v
                                            ? 'bg-indigo-600 text-white border-indigo-600'
                                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600',
                                        i === 0 ? 'rounded-l-md' : '',
                                        i === 3 ? 'rounded-r-md' : '',
                                        i > 0 ? '-ml-px' : ''
                                    ]"
                                    class="px-3 py-1.5 text-xs font-medium border focus:outline-none focus:z-10"
                                    x-text="opt.l">
                            </button>
                        </template>
                    </div>

                    {{-- カテゴリ --}}
                    <select x-model="iconPickerCategory"
                            class="px-3 py-1.5 text-xs bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">{{ __('dixlase-menus::admin.menus.edit.icon_picker.category_all') }}</option>
                        <template x-for="cat in iconCategories" :key="cat">
                            <option :value="cat" x-text="cat"></option>
                        </template>
                    </select>

                    {{-- 結果カウント (ページ範囲付き) --}}
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400"
                          x-text="filteredIconsTotal > iconPickerLimit
                            ? '{{ __('dixlase-menus::admin.menus.edit.icon_picker.results_capped') }}'
                                .replace(':start', iconPickerRangeStart)
                                .replace(':end', iconPickerRangeEnd)
                                .replace(':total', filteredIconsTotal)
                            : '{{ __('dixlase-menus::admin.menus.edit.icon_picker.results') }}'.replace(':total', filteredIconsTotal)">
                    </span>
                </div>
            </div>

            {{-- 読み込み中 --}}
            <div x-show="iconPickerLoading" class="px-6 pb-6 text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                {{ __('dixlase-menus::admin.menus.edit.icon_picker.loading') }}
            </div>

            {{-- アイコングリッド --}}
            <div id="icon-picker-grid"
                 x-show="!iconPickerLoading"
                 class="px-6 pb-4 max-h-96 overflow-y-auto">
                <template x-if="filteredIcons.length === 0">
                    <div class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('dixlase-menus::admin.menus.edit.icon_picker.no_results') }}
                    </div>
                </template>

                <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-1">
                    <template x-for="icon in filteredIcons" :key="icon.s[0] + ':' + icon.n">
                        <button type="button"
                                @click="selectIcon(icon.n, icon.s[0])"
                                :title="icon.l + ' (' + icon.s[0] + ' fa-' + icon.n + ')'"
                                class="aspect-square flex items-center justify-center rounded-md bg-white dark:bg-gray-800 border border-transparent hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            <i :class="icon.s[0] + ' fa-' + icon.n" class="text-base"></i>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Pagination controls. Only rendered when the filtered list
                 exceeds one page; for narrow searches the modal stays
                 visually clean with just the grid. --}}
            <div x-show="!iconPickerLoading && iconPickerPageCount > 1"
                 class="px-6 pt-4 pb-5 flex items-center justify-center gap-3 text-xs border-t border-gray-200 dark:border-gray-700">
                <button type="button"
                        @click="iconPickerPrevPage()"
                        :disabled="iconPickerPage <= 1"
                        class="px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <i class="fas fa-chevron-left mr-1"></i>
                    <span>{{ __('dixlase-menus::admin.menus.edit.icon_picker.prev_page') }}</span>
                </button>
                <span class="text-gray-500 dark:text-gray-400"
                      x-text="'{{ __('dixlase-menus::admin.menus.edit.icon_picker.page_indicator') }}'.replace(':current', iconPickerPage).replace(':total', iconPickerPageCount)">
                </span>
                <button type="button"
                        @click="iconPickerNextPage()"
                        :disabled="iconPickerPage >= iconPickerPageCount"
                        class="px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span>{{ __('dixlase-menus::admin.menus.edit.icon_picker.next_page') }}</span>
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>

        {{-- フッタ: クリア & 閉じる --}}
        <div class="modal-actions">
            <button type="button"
                    @click="clearIcon()"
                    class="mx-2 px-3 py-1.5 text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 focus:outline-none">
                <i class="fas fa-times-circle mr-1"></i>
                {{ __('dixlase-menus::admin.menus.edit.icon_picker.clear') }}
            </button>
            <button type="button"
                    @click="closeIconPicker()"
                    class="mx-2 px-4 py-1.5 text-xs bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md focus:outline-none">
                {{ __('common.close') }}
            </button>
        </div>
    </div>
</div>
