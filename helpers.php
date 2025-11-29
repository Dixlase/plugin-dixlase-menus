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
     * @param array|string $options オプション配列またはメニュースラッグ
     *   - slug: メニューのスラッグ（指定しない場合はデフォルトメニュー）
     *   - location: メニューのロケーション（header, footer, sidebar等）
     *   - template: 使用するテンプレート名 ('default', 'horizontal', 'vertical')
     *   - class: メニューのCSSクラス
     *   - id: メニューのID
     *   - depth: 表示する階層の深さ (0 = 無制限)
     *   - show_children: 子メニューを表示するか (default: true)
     * @return string
     * 
     * 使用例:
     *   {!! dixlase_menu() !!}
     *   {!! dixlase_menu('main-menu') !!}
     *   {!! dixlase_menu(['slug' => 'main-menu', 'template' => 'horizontal']) !!}
     *   {!! dixlase_menu(['location' => 'header']) !!}
     *   {!! dixlase_menu(['class' => 'my-menu', 'id' => 'main-nav']) !!}
     */
    function dixlase_menu(array|string $options = []): string
    {
        // 文字列が渡された場合はスラッグとして扱う
        if (is_string($options)) {
            $options = ['slug' => $options];
        }
        return MenuHelper::render($options);
    }
}

if (!function_exists('dixlase_menu_items')) {
    /**
     * メニューアイテムを配列として取得
     *
     * @param bool $includeChildren 子メニューを含めるか
     * @param string|null $slug メニューのスラッグ（指定しない場合はデフォルトメニュー）
     * @return array
     * 
     * 使用例:
     *   @foreach(dixlase_menu_items() as $item)
     *       <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
     *   @endforeach
     *   @foreach(dixlase_menu_items(true, 'footer-menu') as $item)
     *       ...
     *   @endforeach
     */
    function dixlase_menu_items(bool $includeChildren = true, ?string $slug = null): array
    {
        return MenuHelper::getItems($includeChildren, $slug);
    }
}

if (!function_exists('has_dixlase_menu')) {
    /**
     * メニューが存在するか確認
     *
     * @param string|null $slug メニューのスラッグ（指定しない場合はデフォルトメニュー）
     * @return bool
     * 
     * 使用例:
     *   @if(has_dixlase_menu())
     *       {!! dixlase_menu() !!}
     *   @endif
     *   @if(has_dixlase_menu('footer-menu'))
     *       {!! dixlase_menu('footer-menu') !!}
     *   @endif
     */
    function has_dixlase_menu(?string $slug = null): bool
    {
        return MenuHelper::hasMenu($slug);
    }
}
