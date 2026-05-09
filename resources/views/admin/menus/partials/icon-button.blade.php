{{--
 * Icon picker trigger button
 *
 * Used inline next to each menu item title input. Shows the currently
 * selected icon as a preview, or a placeholder when no icon is set.
 * Clicking opens the shared icon-picker modal targeted at the given item.
 *
 * Variables:
 *   $itemRef - Alpine.js variable name for the current item ('item', 'child', 'grandchild')
 *   $size    - 'md' (default) | 'sm' for compact rows
--}}
@php($size = $size ?? 'md')
@php($height = $size === 'sm' ? 'h-7' : 'h-9')
@php($padX = $size === 'sm' ? 'px-2' : 'px-2.5')
@php($iconSize = $size === 'sm' ? 'text-xs' : 'text-sm')
@php($labelSize = $size === 'sm' ? 'text-[10px]' : 'text-xs')
<button type="button"
        @click="openIconPicker({{ $itemRef }})"
        :title="{{ $itemRef }}.icon_class || '{{ __('dixlase-menus::admin.menus.edit.icon_pick') }}'"
        class="shrink-0 {{ $height }} {{ $padX }} inline-flex items-center gap-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-indigo-500 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <template x-if="{{ $itemRef }}.icon_class">
        <i :class="{{ $itemRef }}.icon_class" class="{{ $iconSize }}"></i>
    </template>
    <template x-if="!{{ $itemRef }}.icon_class">
        <i class="fas fa-icons {{ $iconSize }} text-gray-400 dark:text-gray-500"></i>
    </template>
    <span class="{{ $labelSize }} font-medium whitespace-nowrap">
        {{ __('dixlase-menus::admin.menus.edit.icon_pick') }}
    </span>
</button>
