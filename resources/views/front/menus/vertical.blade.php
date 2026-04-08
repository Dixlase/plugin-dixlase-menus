{{--
This file is part of Dixlase Menus.

Copyright (C) 2026 exc-D inc.
https://exc-d.com

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

@if(!empty($items))
<nav {!! isset($options['id']) ? 'id="' . e($options['id']) . '"' : '' !!}
     class="dixlase-menu dixlase-menu--vertical {{ $options['class'] ?? '' }}"
     aria-label="{{ __('dixlase-menus::front.menu.navigation') }}">
    <ul class="dixlase-menu__list space-y-1">
        @foreach($items as $item)
            @php
                $hasChildren = !empty($item['children']) && ($options['show_children'] ?? true);
                $target = $item['target'] ?? $defaultTarget;
                $url = $item['url'] ?? '#';
                $label = $item['label'] ?? '';
            @endphp
            <li class="dixlase-menu__item {{ $hasChildren ? 'dixlase-menu__item--has-children' : '' }}"
                x-data="{ open: false }">
                <div class="flex items-center">
                    <a href="{{ $url }}"
                       class="dixlase-menu__link flex-1 px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-indigo-400 rounded-lg transition-colors"
                       @if($target !== '_self') target="{{ $target }}" @endif
                       @if($target === '_blank') rel="noopener noreferrer" @endif>
                        {{ $label }}
                    </a>
                    @if($hasChildren)
                        <button type="button"
                                @click="open = !open"
                                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                :aria-expanded="open">
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    @endif
                </div>
                
                @if($hasChildren)
                    <ul class="dixlase-menu__submenu ml-4 mt-1 space-y-1 border-l-2 border-gray-200 dark:border-gray-600"
                        x-show="open"
                        x-collapse>
                        @foreach($item['children'] as $child)
                            @php
                                $childTarget = $child['target'] ?? $defaultTarget;
                                $childUrl = $child['url'] ?? '#';
                                $childLabel = $child['label'] ?? '';
                            @endphp
                            <li class="dixlase-menu__item">
                                <a href="{{ $childUrl }}"
                                   class="dixlase-menu__link block pl-4 py-2 text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors"
                                   @if($childTarget !== '_self') target="{{ $childTarget }}" @endif
                                   @if($childTarget === '_blank') rel="noopener noreferrer" @endif>
                                    {{ $childLabel }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
@endif
