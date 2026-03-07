/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * 共通エントリーポイント（全ページで自動読み込み）
 */

document.addEventListener('alpine:init', () => {
    // メニューアイテムソース切り替え（items/create, items/edit で使用）
    Alpine.data('menuItemSource', () => ({
        sourceType: 'custom_url',
        sourceId: '',
        url: '',

        /**
         * $el.dataset から初期値を取得
         */
        init() {
            const el = this.$el;
            if (el.dataset.sourceType) {
                this.sourceType = el.dataset.sourceType;
            }
            if (el.dataset.sourceId) {
                this.sourceId = el.dataset.sourceId;
            }
            if (el.dataset.url) {
                this.url = el.dataset.url;
            }
        },

        /**
         * ソースタイプ変更時のフィールドリセット
         */
        updateSource() {
            if (this.sourceType === 'custom_url') {
                this.sourceId = '';
            } else {
                this.url = '';
            }
        }
    }));

    // 配置タイプ切り替え（create/edit で使用）
    Alpine.data('menuPlacement', () => ({
        placementType: 'manual',

        /**
         * $el.dataset から初期値を取得
         */
        init() {
            const el = this.$el;
            if (el.dataset.placementType) {
                this.placementType = el.dataset.placementType;
            }
        },

        /**
         * 自動配置かどうか
         */
        get isAuto() {
            return this.placementType === 'auto';
        }
    }));
});
