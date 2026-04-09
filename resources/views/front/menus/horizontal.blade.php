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
     class="dixlase-menu dixlase-menu--horizontal {{ $options['class'] ?? '' }}"
     aria-label="{{ __('dixlase-menus::front.menu.navigation') }}">
    <ul class="dixlase-menu__list flex items-center space-x-6">
        @foreach($items as $item)
            @php
                $hasChildren = !empty($item['children']) && ($options['show_children'] ?? true);
                $target = $item['target'] ?? $defaultTarget;
                $url = $item['url'] ?? '#';
                $label = $item['label'] ?? '';
            @endphp
            <li class="dixlase-menu__item relative group {{ $hasChildren ? 'dixlase-menu__item--has-children' : '' }}">
                <a href="{{ $url }}"
                   class="dixlase-menu__link inline-flex items-center py-2 text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400 transition-colors"
                   @if($target !== '_self') target="{{ $target }}" @endif
                   @if($target === '_blank') rel="noopener noreferrer" @endif>
                    {{ $label }}
                    @if($hasChildren)
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    @endif
                </a>
                
                @if($hasChildren)
                    <ul class="dixlase-menu__submenu absolute left-0 top-full mt-1 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 min-w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        @foreach($item['children'] as $child)
                            @php
                                $childTarget = $child['target'] ?? $defaultTarget;
                                $childUrl = $child['url'] ?? '#';
                                $childLabel = $child['label'] ?? '';
                            @endphp
                            <li class="dixlase-menu__item">
                                <a href="{{ $childUrl }}"
                                   class="dixlase-menu__link block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition-colors"
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
