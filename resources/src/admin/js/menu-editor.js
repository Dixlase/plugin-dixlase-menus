/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * 統合メニューエディター（Alpine.js コンポーネント）
 * edit.blade.php で使用: 配置タイプ切り替え + メニューアイテム管理 + リンクソース + 統合保存
 */

import Sortable from 'sortablejs';

document.addEventListener('alpine:init', () => {
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
    menuGroupLabel: '',
    loadingSources: {},
    linkSourcesUrl: '',

    // アイテム追加モーダル
    addItemModalOpen: false,
    addItemActiveTab: 'custom_url',
    addItemParentIndex: null,
    addItemChildIndex: null,

    // 多言語編集
    availableLocales: [],
    localeNames: {},
    currentLocale: '',

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

        // 多言語編集の初期化
        try {
            this.availableLocales = JSON.parse(el.dataset.availableLocales || '[]');
            this.localeNames = JSON.parse(el.dataset.localeNames || '{}');
        } catch (e) {
            this.availableLocales = [];
            this.localeNames = {};
        }
        this.currentLocale = el.dataset.currentLocale || '';

        // 各アイテムに title_translations を確実に持たせる
        this.normalizeItemTranslations(this.items);

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
     * Open the add item modal
     *
     * @param {number|null} parentIndex - Top-level parent index (null for root)
     * @param {number|null} childIndex - Child index under parent (for grandchild adds)
     */
    openAddItemModal(parentIndex = null, childIndex = null) {
        this.addItemParentIndex = parentIndex !== undefined ? parentIndex : null;
        this.addItemChildIndex = childIndex !== undefined ? childIndex : null;
        this.addItemActiveTab = 'custom_url';
        this.customUrl = { label: '', url: '' };
        this.menuGroupLabel = '';
        this.addItemModalOpen = true;
    },

    /**
     * Get the current target depth where a new item would be inserted
     * (depth of the parent + 1, or 0 for root)
     */
    get addItemTargetDepth() {
        if (this.addItemParentIndex === null) {
            return 0;
        }
        if (this.addItemChildIndex === null) {
            return 1;
        }
        return 2;
    },

    /**
     * Whether the menu_group tab should be available in the modal
     * (a menu_group can only be added if the new item itself can have children)
     */
    get canAddMenuGroup() {
        return this.addItemTargetDepth < this.maxDepth - 1;
    },

    /**
     * Add a new item to the target (root, child, or grandchild)
     *
     * @param {Object} newItem - The item data to add
     */
    _addItemToTarget(newItem) {
        // 新規アイテムには title_translations を必ず初期化
        // 入力された label を現在のロケールの翻訳としてセット
        if (!newItem.title_translations) {
            newItem.title_translations = {};
        }
        if (this.currentLocale && newItem.label) {
            newItem.title_translations[this.currentLocale] = newItem.label;
        }

        // Grandchild: parent + child indices both set
        if (
            this.addItemParentIndex !== null &&
            this.addItemChildIndex !== null &&
            this.items[this.addItemParentIndex] &&
            this.items[this.addItemParentIndex].children &&
            this.items[this.addItemParentIndex].children[this.addItemChildIndex]
        ) {
            const child = this.items[this.addItemParentIndex].children[this.addItemChildIndex];
            if (!child.children) {
                child.children = [];
            }
            newItem.depth = (child.depth || 1) + 1;
            child.children.push(newItem);
            this.$nextTick(() => this.initGrandchildSortables());
            return;
        }

        // Child: only parent index set
        if (this.addItemParentIndex !== null && this.items[this.addItemParentIndex]) {
            const parent = this.items[this.addItemParentIndex];
            if (!parent.children) {
                parent.children = [];
            }
            newItem.depth = (parent.depth || 0) + 1;
            parent.children.push(newItem);
            this.$nextTick(() => this.initChildSortables());
            return;
        }

        // Root
        newItem.depth = 0;
        this.items.push(newItem);
        this.$nextTick(() => this.initSortable());
    },

    /**
     * Add selected source items to the menu
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
                this._addItemToTarget({
                    id: this.generateId(),
                    label: sourceItem.title,
                    url: sourceItem.url,
                    target: '_self',
                    source_type: sourceType,
                    source_id: sourceItem.id,
                    children: []
                });
            }
        });

        // Clear selection
        this.selectedItems[sourceType] = new Set();
        this.selectedItems = { ...this.selectedItems };

        this.addItemModalOpen = false;
    },

    /**
     * Add a custom URL item to the menu
     */
    addCustomUrl() {
        if (!this.customUrl.label || !this.customUrl.url) {
            return;
        }

        this._addItemToTarget({
            id: this.generateId(),
            label: this.customUrl.label,
            url: this.customUrl.url,
            target: '_self',
            source_type: 'custom_url',
            source_id: null,
            children: []
        });

        this.customUrl = { label: '', url: '' };
        this.addItemModalOpen = false;
    },

    /**
     * メニューグループ（ラベルのみ）アイテムを追加
     */
    addMenuGroup() {
        if (!this.menuGroupLabel) {
            return;
        }

        this._addItemToTarget({
            id: this.generateId(),
            label: this.menuGroupLabel,
            url: null,
            target: '_self',
            source_type: 'menu_group',
            source_id: null,
            children: []
        });

        this.menuGroupLabel = '';
        this.addItemModalOpen = false;
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
     *
     * SortableJS の DOM 移動を元に戻してから新しい配列を一括代入する
     * 「revert + atomic assign」パターンで Alpine.js との競合を防止する
     */
    initSortable() {
        const menuList = document.getElementById('menu-items-list');
        if (!menuList || menuList._sortableInitialized) {
            return;
        }

        new Sortable(menuList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            draggable: '> .menu-item',
            onEnd: (evt) => {
                // SortableJS の DOM 移動を元に戻す（Alpine の内部状態と一致させる）
                this.revertSortableDom(evt, '.menu-item');

                // 新しい配列を一括代入（splice 変異ではなく完全置換で確実にリアクティビティ発火）
                const newItems = [...this.items];
                const [moved] = newItems.splice(evt.oldIndex, 1);
                newItems.splice(evt.newIndex, 0, moved);
                this.items = newItems;
            }
        });

        menuList._sortableInitialized = true;
        this.initChildSortables();
    },

    /**
     * SortableJS が移動した DOM 要素を元の位置に戻す
     *
     * from.children には Alpine の <template> マーカーも含まれるため、
     * draggable セレクタでフィルタして正しい位置に戻す
     */
    revertSortableDom(evt, draggableSelector) {
        const { from, item, oldIndex } = evt;
        from.removeChild(item);
        const draggables = from.querySelectorAll(`:scope > ${draggableSelector}`);
        if (oldIndex < draggables.length) {
            from.insertBefore(item, draggables[oldIndex]);
        } else {
            from.appendChild(item);
        }
    },

    /**
     * SortableJSを子リストに初期化
     */
    initChildSortables() {
        this.$nextTick(() => {
            document.querySelectorAll('.children-list').forEach((childList) => {
                if (!childList._sortable) {
                    childList._sortable = new Sortable(childList, {
                        handle: '.child-drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        draggable: '> .child-menu-item',
                        onEnd: (evt) => {
                            const parentKey = childList.getAttribute('data-parent-key');
                            const parent = this.items.find(item => String(item.id) === parentKey);
                            if (!parent || !parent.children) {
                                return;
                            }

                            // SortableJS の DOM 移動を元に戻す
                            this.revertSortableDom(evt, '.child-menu-item');

                            // 新しい配列を一括代入
                            const newChildren = [...parent.children];
                            const [moved] = newChildren.splice(evt.oldIndex, 1);
                            newChildren.splice(evt.newIndex, 0, moved);
                            parent.children = newChildren;
                        }
                    });
                }
            });

            // 孫リストも同時に初期化
            this.initGrandchildSortables();
        });
    },

    /**
     * SortableJSを孫リストに初期化
     *
     * .grandchildren-list は data-grandparent-key（トップ親ID）と
     * data-parent-key（子のID）の両方を保持し、items[].children[].children を辿る。
     */
    initGrandchildSortables() {
        this.$nextTick(() => {
            document.querySelectorAll('.grandchildren-list').forEach((grandList) => {
                if (!grandList._sortable) {
                    grandList._sortable = new Sortable(grandList, {
                        handle: '.grandchild-drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        draggable: '> .grandchild-menu-item',
                        onEnd: (evt) => {
                            const grandparentKey = grandList.getAttribute('data-grandparent-key');
                            const parentKey = grandList.getAttribute('data-parent-key');
                            const grandparent = this.items.find(item => String(item.id) === grandparentKey);
                            if (!grandparent || !grandparent.children) {
                                return;
                            }
                            const child = grandparent.children.find(c => String(c.id) === parentKey);
                            if (!child || !child.children) {
                                return;
                            }

                            this.revertSortableDom(evt, '.grandchild-menu-item');

                            const newGrandchildren = [...child.children];
                            const [moved] = newGrandchildren.splice(evt.oldIndex, 1);
                            newGrandchildren.splice(evt.newIndex, 0, moved);
                            child.children = newGrandchildren;
                        }
                    });
                }
            });
        });
    },

    /**
     * すべてのアイテムに title_translations オブジェクトを確実に持たせる
     * （サーバから読み込んだアイテムは null や undefined のことがある）
     */
    normalizeItemTranslations(items) {
        if (!Array.isArray(items)) return;
        items.forEach((item) => {
            if (!item.title_translations || typeof item.title_translations !== 'object') {
                item.title_translations = {};
            }
            if (Array.isArray(item.children)) {
                this.normalizeItemTranslations(item.children);
            }
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
            title_translations: {},
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
        const parent = this.items[parentIndex];
        if (!parent.children) {
            parent.children = [];
        }
        parent.children.push({
            id: this.generateId(),
            label: '',
            title_translations: {},
            url: '',
            target: '_self',
            source_type: 'custom_url',
            source_id: null,
            depth: (parent.depth || 0) + 1,
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
     * 孫メニューアイテムを削除
     */
    removeGrandchildItem(parentIndex, childIndex, grandchildIndex) {
        this.items[parentIndex]
            .children[childIndex]
            .children.splice(grandchildIndex, 1);
    }
    }));
});
