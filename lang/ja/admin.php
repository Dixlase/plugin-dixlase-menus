<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc.
 * https://exc-d.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

return [
    // 列挙型
    'enums' => [
        'menu_location' => [
            'header' => 'ヘッダー',
            'footer' => 'フッター',
            'sidebar' => 'サイドバー',
        ],
        'placement_type' => [
            'manual' => '手動配置（Blade/ショートコード）',
            'auto' => '自動配置（表示位置指定）',
            'manual_description' => '@menuディレクティブまたは[menu]ショートコードでテンプレートに配置',
            'auto_description' => '選択した表示位置に自動的に表示',
        ],
    ],

    // メニュー一覧
    'menus' => [
        'index' => [
            'heading' => 'メニュー一覧',
            'create_menu' => 'メニューを作成',
            'clear_cache' => 'キャッシュをクリア',
            'no_menus' => 'メニューがありません',
            'no_menus_description' => '最初のメニューを作成してください',
            'create_first_menu' => '最初のメニューを作成',
            'table' => [
                'name' => 'メニュー名',
                'location' => '表示位置',
                'items' => 'アイテム',
                'items_count' => '件',
                'status' => 'ステータス',
                'actions' => 'アクション',
                'no_location' => '未設定',
            ],
        ],
        'create' => [
            'heading' => 'メニューを作成',
            'basic_info' => '基本情報',
            'name' => 'メニュー名',
            'name_help' => 'メニューの識別名を入力してください',
            'slug' => 'スラッグ',
            'slug_help' => 'URLやコードで使用する識別子（小文字の英数字、ハイフン、アンダースコアのみ）',
            'description' => '説明',
            'description_help' => 'メニューの説明（管理画面でのみ表示されます）',
            'display_settings' => '表示設定',
            'placement_type' => '配置方法',
            'location' => '表示位置',
            'location_help' => 'このメニューを表示する位置を選択してください',
            'select_location' => '位置を選択',
            'display_order' => '表示順',
            'display_order_help' => '同じ位置に複数のメニューがある場合の表示順序（小さい数字が先に表示されます）',
            'is_active' => 'メニューを有効化',
            'is_active_help' => 'チェックを外すとメニューが非表示になります',
            'confirm_title' => 'メニューの作成',
            'confirm_message' => 'このメニューを作成してもよろしいですか？',
            'manual_create_hint' => '保存後、編集画面にBladeディレクティブとショートコードが表示されます。',
        ],
        'edit' => [
            'heading' => 'メニューを編集',
            'basic_info' => '基本情報',
            'name' => 'メニュー名',
            'slug' => 'スラッグ',
            'description' => '説明',
            'display_settings' => '表示設定',
            'placement_type' => '配置方法',
            'location' => '表示位置',
            'select_location' => '位置を選択',
            'display_order' => '表示順',
            'is_active' => 'メニューを有効化',
            'placement_code' => '配置コード',
            'blade_directive' => 'Bladeディレクティブ',
            'shortcode' => 'ショートコード',
            'menu_items' => 'メニューアイテム',
            'add_item' => 'アイテムを追加',
            'add_items' => 'アイテムを追加',
            'custom_url' => 'カスタムURL',
            'search_items' => '検索...',
            'add_to_menu' => 'メニューに追加',
            'no_source_items' => 'アイテムが見つかりません',
            'loading_items' => '読み込み中...',
            'custom_url_label' => 'リンクテキスト',
            'custom_url_url' => 'https://example.com または /about',
            'no_items' => 'メニューアイテムがありません',
            'no_items_description' => '最初のメニューアイテムを追加してください',
            'add_first_item' => '最初のアイテムを追加',
            'children' => '個の子アイテム',
            'order' => '順序',
            'add_child' => '子アイテムを追加',
            'add_child_hint' => 'の子アイテムとして追加',
            'published_only_hint' => '公開済みのページのみ表示されます。',
            'menu_group' => 'メニューグループ',
            'menu_group_label' => 'グループ名',
            'menu_group_label_placeholder' => '例: 製品情報、サービス',
            'menu_group_hint' => 'メニューグループはリンクなしのラベル専用アイテムです。ドロップダウンやメガメニューの親コンテナとして使用します。',
            'menu_group_badge' => 'グループ',
            'toggle_children' => '子アイテムを展開/折りたたみ',
            'confirm_title' => 'メニューの保存',
            'confirm_message' => 'このメニューの変更を保存してもよろしいですか？',
            'sidebar_open' => 'サイドバーを開く',
            'sidebar_close' => 'サイドバーを閉じる',
        ],
        'delete_confirm_title' => 'メニューの削除',
        'delete_confirm_message' => 'このメニューを削除してもよろしいですか？メニューアイテムも含めて削除されます。',
    ],

    // メニューアイテム
    'menu_items' => [
        'create' => [
            'heading' => 'メニューアイテムを追加',
            'parent_item' => '親アイテム',
            'basic_info' => '基本情報',
            'title' => 'タイトル',
            'title_help' => 'メニューに表示されるテキスト',
            'link_settings' => 'リンク設定',
            'source_type' => 'リンクタイプ',
            'url' => 'URL',
            'url_help' => '完全なURLまたは相対パス（例: https://example.com または /about）',
            'source_id' => 'ソースID',
            'source_id_help' => 'リンク先のページやカテゴリのID',
            'target' => 'リンクターゲット',
            'target_default' => 'デフォルト設定を使用',
            'display_settings' => '表示設定',
            'css_class' => 'CSSクラス',
            'css_class_help' => 'カスタムCSSクラス（スペース区切りで複数指定可能）',
            'icon_class' => 'アイコンクラス',
            'icon_class_help' => 'Font Awesomeなどのアイコンクラス（例: fas fa-home）',
            'display_order' => '表示順',
            'is_active' => 'アイテムを有効化',
            'is_active_help' => 'チェックを外すとアイテムが非表示になります',
        ],
        'edit' => [
            'heading' => 'メニューアイテムを編集',
            'basic_info' => '基本情報',
            'title' => 'タイトル',
            'link_settings' => 'リンク設定',
            'source_type' => 'リンクタイプ',
            'url' => 'URL',
            'source_id' => 'ソースID',
            'target' => 'リンクターゲット',
            'target_default' => 'デフォルト設定を使用',
            'display_settings' => '表示設定',
            'css_class' => 'CSSクラス',
            'icon_class' => 'アイコンクラス',
            'display_order' => '表示順',
            'is_active' => 'アイテムを有効化',
        ],
    ],

    // 設定画面
    'settings' => [
        'heading' => 'メニュー設定',

        'basic' => [
            'title' => '基本設定',
            'menu_structure' => 'メニュー構造',
            'max_menu_depth' => '最大階層深度',
            'max_menu_depth_help' => 'メニューの階層の深さを設定します（1〜10）',
            'default_target' => 'デフォルトリンクターゲット',
            'default_target_help' => 'メニューアイテムのデフォルトのリンクターゲットを設定します',
            'target_self' => '同じウィンドウ (_self)',
            'target_blank' => '新しいウィンドウ (_blank)',
            'target_parent' => '親フレーム (_parent)',
            'target_top' => '最上位フレーム (_top)',
        ],

        'cache' => [
            'title' => 'キャッシュ設定',
            'menu_cache' => 'メニューキャッシュ',
            'enable_menu_cache' => 'メニューキャッシュを有効化',
            'enable_menu_cache_help' => 'メニューのキャッシュを有効にしてパフォーマンスを向上させます',
            'cache_duration' => 'キャッシュ有効期間（秒）',
            'cache_duration_help' => 'メニューキャッシュの有効期間を秒単位で設定します（60〜86400）',
            'clear_cache' => 'キャッシュをクリア',
            'clear_cache_help' => 'すべてのメニューキャッシュを削除します',
            'clear_cache_confirm' => '本当にキャッシュをクリアしますか？',
        ],

        'menu_items' => [
            'title' => 'メニューアイテム',
            'add_item' => 'メニューを追加',
            'add_child' => '子メニューを追加',
            'label' => 'ラベル',
            'label_placeholder' => 'メニューに表示するテキスト',
            'url' => 'URL',
            'url_placeholder' => 'https://example.com または /about',
            'target' => 'ターゲット',
            'source_type' => 'リンクタイプ',
            'source_custom' => 'カスタムURL',
            'source_select' => 'コンテンツを選択',
            'select_content' => 'コンテンツを選択...',
            'no_providers' => '利用可能なコンテンツプロバイダーがありません',
            'items_help' => 'メニューアイテムを追加してください。ドラッグ＆ドロップで順序を変更できます。',
        ],

        'confirm' => [
            'title' => '設定の保存',
            'message' => 'メニュー設定を保存してもよろしいですか？',
        ],
    ],

    // コントローラーメッセージ
    'messages' => [
        // メニュー
        'menu_created' => 'メニューを作成しました',
        'menu_updated' => 'メニューを更新しました',
        'menu_deleted' => 'メニューを削除しました',
        'menu_restored' => 'メニューを復元しました',
        'menu_not_found' => 'メニューが見つかりません',
        'slug_already_exists' => 'このスラッグは既に使用されています',

        // メニューアイテム
        'menu_item_created' => 'メニューアイテムを作成しました',
        'menu_item_updated' => 'メニューアイテムを更新しました',
        'menu_item_deleted' => 'メニューアイテムを削除しました',
        'menu_item_moved' => 'メニューアイテムを移動しました',
        'menu_item_not_found' => 'メニューアイテムが見つかりません',
        'max_depth_exceeded' => '最大階層深度を超えています',
        'cannot_set_self_as_parent' => '自分自身を親に設定できません',
        'cannot_set_descendant_as_parent' => '子孫を親に設定できません',

        // 並び順
        'order_updated' => '並び順を更新しました',
        'order_update_failed' => '並び順の更新に失敗しました',
        'no_items_to_update' => '更新するアイテムがありません',
        'move_failed' => '移動に失敗しました',

        // 設定
        'settings_updated' => '設定を更新しました',
        'cache_cleared' => 'キャッシュをクリアしました',

        // 一括保存
        'menu_items_saved' => 'メニューアイテムを保存しました',
        'menu_items_save_failed' => 'メニューアイテムの保存に失敗しました',
    ],
];
