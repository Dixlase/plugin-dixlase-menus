{{--
This file is part of Dixlase Menus.

Copyright (C) 2026 exc-D inc.
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

{{--
/**
 * メニューアイテムパーシャル（再帰的に子メニューを表示）
 * 
 * 利用可能な変数:
 *   $item - メニューアイテム
 *   $depth - 現在の階層の深さ
 *   $options - 表示オプション
 *   $defaultTarget - デフォルトのリンクターゲット
 */
--}}

@php
    $hasChildren = !empty($item['children']) && ($options['show_children'] ?? true);
    $maxDepth = $options['depth'] ?? 0;
    $showChildren = $hasChildren && ($maxDepth === 0 || $depth < $maxDepth);
    $isMenuGroup = ($item['source_type'] ?? '') === 'menu_group';
    $target = $item['target'] ?? $defaultTarget;
    $url = $item['url'] ?? '#';
    $label = $item['label'] ?? '';
@endphp

<li class="dixlase-menu__item {{ $showChildren ? 'dixlase-menu__item--has-children' : '' }} {{ $isMenuGroup ? 'dixlase-menu__item--menu-group' : '' }} dixlase-menu__item--depth-{{ $depth }}">
    @if($isMenuGroup)
        <span class="dixlase-menu__link dixlase-menu__link--menu-group" role="button" aria-haspopup="true">
            {{ $label }}
        </span>
    @else
        <a href="{{ $url }}"
           class="dixlase-menu__link"
           @if($target !== '_self') target="{{ $target }}" @endif
           @if($target === '_blank') rel="noopener noreferrer" @endif>
            {{ $label }}
        </a>
    @endif
    
    @if($showChildren)
        <ul class="dixlase-menu__submenu">
            @foreach($item['children'] as $child)
                @include('dixlase-menus::front.menus.partials.menu-item', [
                    'item' => $child,
                    'depth' => $depth + 1,
                    'options' => $options,
                    'defaultTarget' => $defaultTarget,
                ])
            @endforeach
        </ul>
    @endif
</li>
