{{--
/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * デフォルトメニューテンプレート
 * 
 * 利用可能な変数:
 *   $items - メニューアイテムの配列
 *   $options - 表示オプション
 *   $defaultTarget - デフォルトのリンクターゲット
 */
--}}

@if(!empty($items))
<nav {!! isset($options['id']) ? 'id="' . e($options['id']) . '"' : '' !!}
     class="dixlase-menu {{ $options['class'] ?? '' }}"
     aria-label="{{ __('dixlase-menu::front.menu.navigation') }}">
    <ul class="dixlase-menu__list">
        @foreach($items as $item)
            @include('dixlase-menu::front.menus.partials.menu-item', [
                'item' => $item,
                'depth' => 0,
                'options' => $options,
                'defaultTarget' => $defaultTarget,
            ])
        @endforeach
    </ul>
</nav>
@endif
