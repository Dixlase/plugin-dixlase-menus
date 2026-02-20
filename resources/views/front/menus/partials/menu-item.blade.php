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
    $target = $item['target'] ?? $defaultTarget;
    $url = $item['url'] ?? '#';
    $label = $item['label'] ?? '';
@endphp

<li class="dixlase-menu__item {{ $showChildren ? 'dixlase-menu__item--has-children' : '' }} dixlase-menu__item--depth-{{ $depth }}">
    <a href="{{ $url }}"
       class="dixlase-menu__link"
       @if($target !== '_self') target="{{ $target }}" @endif
       @if($target === '_blank') rel="noopener noreferrer" @endif>
        {{ $label }}
    </a>
    
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
