/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * 統合メニューエディター（Alpine.js コンポーネント）
 * edit.blade.php で使用: 配置タイプ切り替え + メニューアイテム管理 + リンクソース + 統合保存
 */

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

Alpine.data('menuEditor', () => ({
    // 配置タイプ
    placementType: 'manual',
    placementDescriptions: {},

    // メニューアイテム管理
    items: [],
    menuId: null,
    maxDepth: 3,
    syncUrl: '',
    saving: false,
    message: '',
    messageType: 'success',
    hasChanges: false,
    sortableInstance: null,

    // リンクソース管理
    linkSources: [],
    sourceItems: {},
    sourceSearch: {},
    selectedItems: {},
    customUrl: { label: '', url: '' },
    loadingSources: {},
    linkSourcesUrl: '',

    /**
     * 自動配置かどうか
     */
    get isAuto() {
        return this.placementType === 'auto';
    },

    /**
     * 現在の配置タイプの説明文を取得
     */
    get placementDescription() {
        return this.placementDescriptions[this.placementType] || '';
    },

    /**
     * 初期化: $el.dataset からサーバーデータを取得し、submitModalForm をオーバーライド
     */
    init() {
        this.$dispatch('right-sidebar-active');
        const el = this.$el;

        // 配置タイプ初期化
        if (el.dataset.placementType) {
            this.placementType = el.dataset.placementType;
        }

        // 配置タイプ説明文の初期化
        try {
            this.placementDescriptions = JSON.parse(el.dataset.placementDescriptions || '{}');
        } catch (e) {
            this.placementDescriptions = {};
        }

        // メニューアイテム初期化
        this.menuId = parseInt(el.dataset.menuId);
        this.maxDepth = parseInt(el.dataset.maxDepth) || 3;
        this.syncUrl = el.dataset.syncUrl;

        try {
            this.items = JSON.parse(el.dataset.items || '[]');
        } catch (e) {
            console.error('Failed to parse menu items:', e);
            this.items = [];
        }

        // リンクソースURL初期化
        this.linkSourcesUrl = el.dataset.linkSourcesUrl || '';

        this.$nextTick(() => {
            this.initSortable();
        });

        // 変更を監視
        this.$watch('items', () => {
            this.hasChanges = true;
        }, { deep: true });

        // モーダル確認時に統合保存を実行するためオーバーライド
        const originalSubmitModalForm = window.submitModalForm;
        const self = this;
        window.submitModalForm = function (formId) {
            if (formId === 'menu-form') {
                self.saveAll();
            } else {
                originalSubmitModalForm(formId);
            }
        };

        // リンクソースを読み込み
        this.loadLinkSources();
    },

    /**
     * リンクソースのメタデータを取得
     */
    async loadLinkSources() {
        if (!this.linkSourcesUrl) {
            return;
        }

        try {
            const response = await fetch(this.linkSourcesUrl, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            this.linkSources = data.sources || [];
        } catch (error) {
            console.error('Failed to load link sources:', error);
        }
    },

    /**
     * 特定ソースのアイテムを読み込み（遅延ロード）
     */
    async loadSourceItems(sourceType) {
        if (this.sourceItems[sourceType]) {
            return;
        }

        this.loadingSources[sourceType] = true;

        try {
            const url = `${this.linkSourcesUrl}/${sourceType}/items`;
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            this.sourceItems[sourceType] = data.items || [];
        } catch (error) {
            console.error(`Failed to load items for ${sourceType}:`, error);
            this.sourceItems[sourceType] = [];
        } finally {
            this.loadingSources[sourceType] = false;
        }
    },

    /**
     * ソース内アイテムを検索（デバウンスはBladeの @input.debounce で処理）
     */
    async searchSourceItems(sourceType) {
        this.loadingSources[sourceType] = true;

        try {
            const query = this.sourceSearch[sourceType] || '';
            const url = `${this.linkSourcesUrl}/${sourceType}/items?search=${encodeURIComponent(query)}`;
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            this.sourceItems[sourceType] = data.items || [];
        } catch (error) {
            console.error(`Failed to search items for ${sourceType}:`, error);
        } finally {
            this.loadingSources[sourceType] = false;
        }
    },

    /**
     * チェックボックスのトグル
     */
    toggleSourceItem(sourceType, itemId) {
        if (!this.selectedItems[sourceType]) {
            this.selectedItems[sourceType] = new Set();
        }

        if (this.selectedItems[sourceType].has(itemId)) {
            this.selectedItems[sourceType].delete(itemId);
        } else {
            this.selectedItems[sourceType].add(itemId);
        }

        // リアクティビティのためにオブジェクトを再割当て
        this.selectedItems = { ...this.selectedItems };
    },

    /**
     * 選択されたアイテムをメニューに追加
     */
    addSelectedItems(sourceType) {
        const selected = this.selectedItems[sourceType];
        if (!selected || selected.size === 0) {
            return;
        }

        const items = this.sourceItems[sourceType] || [];

        selected.forEach((itemId) => {
            const sourceItem = items.find(i => i.id === itemId);
            if (sourceItem) {
                this.items.push({
                    id: this.generateId(),
                    label: sourceItem.title,
                    url: sourceItem.url,
                    target: '_self',
                    source_type: sourceType,
                    source_id: sourceItem.id,
                    depth: 0,
                    children: []
                });
            }
        });

        // 選択をクリア
        this.selectedItems[sourceType] = new Set();
        this.selectedItems = { ...this.selectedItems };

        this.$nextTick(() => this.initSortable());
    },

    /**
     * カスタムURLアイテムをメニューに追加
     */
    addCustomUrl() {
        if (!this.customUrl.label || !this.customUrl.url) {
            return;
        }

        this.items.push({
            id: this.generateId(),
            label: this.customUrl.label,
            url: this.customUrl.url,
            target: '_self',
            source_type: 'custom_url',
            source_id: null,
            depth: 0,
            children: []
        });

        this.customUrl = { label: '', url: '' };
        this.$nextTick(() => this.initSortable());
    },

    /**
     * 統合保存: メニューアイテムをAJAX保存後、フォームをPOST送信
     */
    async saveAll() {
        if (this.hasChanges && this.items.length > 0) {
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

                if (!data.success) {
                    this.message = data.message || this.$el.dataset.errorMessage;
                    this.messageType = 'error';
                    this.saving = false;
                    return;
                }

                if (data.items) {
                    this.items = data.items;
                }
                this.hasChanges = false;
            } catch (error) {
                console.error('Save error:', error);
                this.message = this.$el.dataset.errorMessage;
                this.messageType = 'error';
                this.saving = false;
                return;
            }

            this.saving = false;
        }

        // フォームをネイティブ送信（@submit.prevent をバイパス）
        this.$refs.menuForm.submit();
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
     * 親メニューアイテムを追加（空のカスタムURL行）
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
    }
}));
