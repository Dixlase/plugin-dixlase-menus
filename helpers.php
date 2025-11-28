<?php

/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * グローバルヘルパー関数
 */

use Plugins\DixlaseMenu\App\Helpers\MenuHelper;

if (!function_exists('dixlase_menu')) {
    /**
     * メニューをHTMLとしてレンダリング
     *
     * @param array $options オプション
     *   - template: 使用するテンプレート名 ('default', 'horizontal', 'vertical')
     *   - class: メニューのCSSクラス
     *   - id: メニューのID
     *   - depth: 表示する階層の深さ (0 = 無制限)
     *   - show_children: 子メニューを表示するか (default: true)
     * @return string
     * 
     * 使用例:
     *   {!! dixlase_menu() !!}
     *   {!! dixlase_menu(['template' => 'horizontal']) !!}
     *   {!! dixlase_menu(['class' => 'my-menu', 'id' => 'main-nav']) !!}
     */
    function dixlase_menu(array $options = []): string
    {
        return MenuHelper::render($options);
    }
}

if (!function_exists('dixlase_menu_items')) {
    /**
     * メニューアイテムを配列として取得
     *
     * @param bool $includeChildren 子メニューを含めるか
     * @return array
     * 
     * 使用例:
     *   @foreach(dixlase_menu_items() as $item)
     *       <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
     *   @endforeach
     */
    function dixlase_menu_items(bool $includeChildren = true): array
    {
        return MenuHelper::getItems($includeChildren);
    }
}

if (!function_exists('has_dixlase_menu')) {
    /**
     * メニューが存在するか確認
     *
     * @return bool
     * 
     * 使用例:
     *   @if(has_dixlase_menu())
     *       {!! dixlase_menu() !!}
     *   @endif
     */
    function has_dixlase_menu(): bool
    {
        return MenuHelper::hasMenu();
    }
}
