/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * メニューアイテムエディター（Alpine.js コンポーネント）
 * edit.blade.php の右カラムで使用
 */

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

Alpine.data('menuItemsEditor', () => ({
    items: [],
    menuId: null,
    maxDepth: 3,
    syncUrl: '',
    saving: false,
    message: '',
    messageType: 'success',
    hasChanges: false,
    sortableInstance: null,

    /**
     * 初期化: $el.dataset からサーバーデータを取得
     */
    init() {
        const el = this.$el;
        this.menuId = parseInt(el.dataset.menuId);
        this.maxDepth = parseInt(el.dataset.maxDepth) || 3;
        this.syncUrl = el.dataset.syncUrl;

        try {
            this.items = JSON.parse(el.dataset.items || '[]');
        } catch (e) {
            console.error('Failed to parse menu items:', e);
            this.items = [];
        }

        this.$nextTick(() => {
            this.initSortable();
        });

        // 変更を監視
        this.$watch('items', () => {
            this.hasChanges = true;
        }, { deep: true });
    },

    /**
     * SortableJSを親リストに初期化
     */
    initSortable() {
        const menuList = document.getElementById('menu-items-list');
        if (menuList) {
            this.sortableInstance = new Sortable(menuList, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: (evt) => {
                    const item = this.items.splice(evt.oldIndex, 1)[0];
                    this.items.splice(evt.newIndex, 0, item);
                }
            });

            this.initChildSortables();
        }
    },

    /**
     * SortableJSを子リストに初期化
     */
    initChildSortables() {
        this.$nextTick(() => {
            document.querySelectorAll('.children-list').forEach((childList) => {
                if (!childList._sortable) {
                    const parentIndex = parseInt(childList.dataset.parentIndex);
                    childList._sortable = new Sortable(childList, {
                        handle: '.child-drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        onEnd: (evt) => {
                            if (this.items[parentIndex] && this.items[parentIndex].children) {
                                const child = this.items[parentIndex].children.splice(evt.oldIndex, 1)[0];
                                this.items[parentIndex].children.splice(evt.newIndex, 0, child);
                            }
                        }
                    });
                }
            });
        });
    },

    /**
     * 一意なIDを生成
     */
    generateId() {
        return 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    },

    /**
     * 親メニューアイテムを追加
     */
    addItem() {
        this.items.push({
            id: this.generateId(),
            label: '',
            url: '',
            target: '_self',
            source_type: 'custom_url',
            source_id: null,
            depth: 0,
            children: []
        });
        this.$nextTick(() => this.initSortable());
    },

    /**
     * 親メニューアイテムを削除
     */
    removeItem(index) {
        this.items.splice(index, 1);
    },

    /**
     * 子メニューアイテムを追加
     */
    addChildItem(parentIndex) {
        if (!this.items[parentIndex].children) {
            this.items[parentIndex].children = [];
        }
        this.items[parentIndex].children.push({
            id: this.generateId(),
            label: '',
            url: '',
            target: '_self',
            source_type: 'custom_url',
            source_id: null,
            depth: 1,
            children: []
        });
        this.$nextTick(() => this.initChildSortables());
    },

    /**
     * 子メニューアイテムを削除
     */
    removeChildItem(parentIndex, childIndex) {
        this.items[parentIndex].children.splice(childIndex, 1);
    },

    /**
     * メニューアイテムをサーバーに保存
     */
    async saveItems() {
        this.saving = true;
        this.message = '';

        try {
            const response = await fetch(this.syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ items: this.items })
            });

            const data = await response.json();

            if (data.success) {
                this.message = data.message;
                this.messageType = 'success';
                this.hasChanges = false;

                // 返されたアイテムで更新（IDが割り当てられる）
                if (data.items) {
                    this.items = data.items;
                }
            } else {
                this.message = data.message || this.$el.dataset.errorMessage || 'Failed to save';
                this.messageType = 'error';
            }
        } catch (error) {
            console.error('Save error:', error);
            this.message = this.$el.dataset.errorMessage || 'Failed to save';
            this.messageType = 'error';
        } finally {
            this.saving = false;

            // メッセージを5秒後に消す
            setTimeout(() => {
                this.message = '';
            }, 5000);
        }
    }
}));
