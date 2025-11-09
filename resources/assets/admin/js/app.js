/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * Main Application Entry Point
 */

// メニューエディター
import MenuEditor from './menu-editor.js';
import MenuList from './menu-list.js';
import MenuForm from './menu-form.js';

// グローバルに公開
window.MenuEditor = MenuEditor;
window.MenuList = MenuList;
window.MenuForm = MenuForm;

// Alpine.js用のデータ
document.addEventListener('alpine:init', () => {
    // メニュー位置管理
    Alpine.data('menuLocations', () => ({
        locations: [],
        
        init() {
            // 初期データは各ビューから渡される
        },
        
        addLocation() {
            this.locations.push({
                key: '',
                label: ''
            });
        },
        
        removeLocation(index) {
            this.locations.splice(index, 1);
        }
    }));
    
    // メニューアイテムソース選択
    Alpine.data('menuItemSource', () => ({
        sourceType: 'custom_url',
        sourceId: '',
        url: '',
        
        init() {
            // 初期データは各ビューから渡される
        },
        
        updateSource() {
            if (this.sourceType === 'custom_url') {
                this.sourceId = '';
            } else {
                this.url = '';
            }
        }
    }));
});
