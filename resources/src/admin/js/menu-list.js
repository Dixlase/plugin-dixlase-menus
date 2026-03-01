/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * メニュー一覧（削除・復元確認）
 */

class MenuList {
    /**
     * @param {Object} options
     */
    constructor(options = {}) {
        this.container = options.container || document.querySelector('.menu-list-container');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        this.init();
    }

    /**
     * 初期化
     */
    init() {
        if (!this.container) {
            return;
        }
        this.setupEventListeners();
    }

    /**
     * イベントリスナーのセットアップ
     */
    setupEventListeners() {
        // 復元ボタン
        this.container.addEventListener('click', (e) => {
            const btn = e.target.closest('.restore-menu-btn');
            if (btn) {
                e.preventDefault();
                this.confirmRestore(btn);
            }
        });
    }

    /**
     * 復元確認 — data属性からURLを取得
     */
    confirmRestore(btn) {
        const menuName = btn.dataset.menuName;
        const restoreUrl = btn.dataset.restoreUrl;

        if (!confirm(`「${menuName}」を復元しますか？`)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = restoreUrl;

        form.innerHTML = `<input type="hidden" name="_token" value="${this.csrfToken}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

// DOMContentLoaded時に自動初期化
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.menu-list-container')) {
        new MenuList();
    }
});
