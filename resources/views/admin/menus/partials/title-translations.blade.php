{{--
 * Title translations editor (per-locale label inputs)
 *
 * Renders compact per-locale title inputs underneath the main label input.
 * Hidden when only one locale is registered (single-language site).
 *
 * Variables:
 *   $itemRef - the Alpine.js variable name referencing the current item
 *              (e.g. 'item', 'child', 'grandchild')
--}}
<div x-show="availableLocales.length > 1"
     class="mt-2 space-y-1">
    <template x-for="loc in availableLocales" :key="loc">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-600 text-[10px] font-mono uppercase text-gray-700 dark:text-gray-300 w-10 shrink-0"
                  x-text="loc"></span>
            <input type="text"
                   :value="(({{ $itemRef }}.title_translations || {})[loc]) || ''"
                   @input="if (!{{ $itemRef }}.title_translations) {{ $itemRef }}.title_translations = {}; {{ $itemRef }}.title_translations[loc] = $event.target.value"
                   :placeholder="(localeNames[loc] || loc) + ' / ' + (loc === currentLocale ? '{{ __('dixlase-menus::admin.menus.edit.translation_primary') }}' : '{{ __('dixlase-menus::admin.menus.edit.translation_other') }}')"
                   class="block flex-1 px-2 py-1 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </template>
</div>
