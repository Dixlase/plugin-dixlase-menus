{{--
This file is part of Dixlase Menus.

Copyright (C) 2026 exc-D inc. and Dixlase contributors
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

@if(!empty($items))
<nav {!! isset($options['id']) ? 'id="' . e($options['id']) . '"' : '' !!}
     class="dixlase-menu {{ $options['class'] ?? '' }}"
     aria-label="{{ __('dixlase-menus::front.menu.navigation') }}">
    <ul class="dixlase-menu__list">
        @foreach($items as $item)
            @include('dixlase-menus::front.menus.partials.menu-item', [
                'item' => $item,
                'depth' => 0,
                'options' => $options,
                'defaultTarget' => $defaultTarget,
            ])
        @endforeach
    </ul>
</nav>
@endif
