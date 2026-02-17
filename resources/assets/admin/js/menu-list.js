/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * Menu List - メニュー一覧管理
 */

class MenuList {
    constructor(options = {}) {
        this.csrfToken = options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
        this.init();
    }
    
    /**
     * 初期化
     */
    init() {
        this.setupEventListeners();
    }
    
    /**
     * イベントリスナーのセットアップ
     */
    setupEventListeners() {
        // 削除ボタン
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-menu-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.delete-menu-btn');
                const menuId = btn.dataset.menuId;
                const menuName = btn.dataset.menuName;
                
                this.confirmDelete(menuId, menuName);
            }
        });
        
        // 復元ボタン
        document.addEventListener('click', (e) => {
            if (e.target.closest('.restore-menu-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.restore-menu-btn');
                const menuId = btn.dataset.menuId;
                const menuName = btn.dataset.menuName;
                
                this.confirmRestore(menuId, menuName);
            }
        });
    }
    
    /**
     * 削除確認
     */
    confirmDelete(menuId, menuName) {
        if (!confirm(`「${menuName}」を削除しますか？\n\nメニューアイテムも含めて削除されます。`)) {
            return;
        }
        
        // 削除フォームを送信
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/menus/${menuId}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = this.csrfToken;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
    
    /**
     * 復元確認
     */
    confirmRestore(menuId, menuName) {
        if (!confirm(`「${menuName}」を復元しますか？`)) {
            return;
        }
        
        // 復元フォームを送信
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/menus/${menuId}/restore`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = this.csrfToken;
        
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
    
}

// グローバルに公開
window.MenuList = MenuList;

// DOMContentLoaded時に自動初期化
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.menu-list-container')) {
        new MenuList();
    }
});
