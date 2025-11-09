/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * Menu Editor - ドラッグ&ドロップによるメニューアイテム管理
 */

class MenuEditor {
    constructor(options = {}) {
        this.menuId = options.menuId;
        this.container = options.container || document.getElementById('menu-items-container');
        this.maxDepth = options.maxDepth || 3;
        this.updateOrderUrl = options.updateOrderUrl;
        this.moveItemUrl = options.moveItemUrl;
        this.csrfToken = options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
        
        this.draggedItem = null;
        this.draggedOverItem = null;
        
        this.init();
    }
    
    /**
     * 初期化
     */
    init() {
        if (!this.container) {
            console.warn('Menu items container not found');
            return;
        }
        
        this.setupDragAndDrop();
        this.setupEventListeners();
    }
    
    /**
     * ドラッグ&ドロップのセットアップ
     */
    setupDragAndDrop() {
        const items = this.container.querySelectorAll('.menu-item');
        
        items.forEach(item => {
            item.setAttribute('draggable', 'true');
            
            // ドラッグ開始
            item.addEventListener('dragstart', (e) => {
                this.draggedItem = item;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', item.innerHTML);
            });
            
            // ドラッグ終了
            item.addEventListener('dragend', (e) => {
                item.classList.remove('dragging');
                this.clearDropIndicators();
                this.draggedItem = null;
                this.draggedOverItem = null;
            });
            
            // ドラッグオーバー
            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                if (this.draggedItem === item) return;
                
                this.clearDropIndicators();
                this.draggedOverItem = item;
                
                const rect = item.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    item.classList.add('drop-before');
                } else {
                    item.classList.add('drop-after');
                }
            });
            
            // ドラッグリーブ
            item.addEventListener('dragleave', (e) => {
                if (e.target === item) {
                    item.classList.remove('drop-before', 'drop-after');
                }
            });
            
            // ドロップ
            item.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                if (this.draggedItem === item) return;
                
                this.handleDrop(item, e);
            });
        });
    }
    
    /**
     * ドロップ処理
     */
    handleDrop(targetItem, event) {
        const rect = targetItem.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;
        const dropBefore = event.clientY < midpoint;
        
        // 深さチェック
        const targetDepth = parseInt(targetItem.dataset.depth || 0);
        const draggedDepth = parseInt(this.draggedItem.dataset.depth || 0);
        
        if (targetDepth >= this.maxDepth && !dropBefore) {
            this.showError('最大階層深度を超えることはできません');
            this.clearDropIndicators();
            return;
        }
        
        // DOM操作
        if (dropBefore) {
            targetItem.parentNode.insertBefore(this.draggedItem, targetItem);
        } else {
            targetItem.parentNode.insertBefore(this.draggedItem, targetItem.nextSibling);
        }
        
        // サーバーに保存
        this.saveOrder();
        
        this.clearDropIndicators();
    }
    
    /**
     * ドロップインジケーターをクリア
     */
    clearDropIndicators() {
        const items = this.container.querySelectorAll('.menu-item');
        items.forEach(item => {
            item.classList.remove('drop-before', 'drop-after');
        });
    }
    
    /**
     * 並び順を保存
     */
    async saveOrder() {
        const items = this.container.querySelectorAll('.menu-item');
        const orderData = [];
        
        items.forEach((item, index) => {
            orderData.push({
                id: parseInt(item.dataset.id),
                order: index
            });
        });
        
        try {
            const response = await fetch(this.updateOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ items: orderData })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('並び順を更新しました');
            } else {
                this.showError(data.message || '並び順の更新に失敗しました');
            }
        } catch (error) {
            console.error('Error saving order:', error);
            this.showError('並び順の更新に失敗しました');
        }
    }
    
    /**
     * アイテムを移動（親変更）
     */
    async moveItem(itemId, newParentId) {
        try {
            const response = await fetch(this.moveItemUrl.replace(':id', itemId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ parent_id: newParentId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('アイテムを移動しました');
                // ページをリロードして階層を更新
                setTimeout(() => location.reload(), 500);
            } else {
                this.showError(data.message || 'アイテムの移動に失敗しました');
            }
        } catch (error) {
            console.error('Error moving item:', error);
            this.showError('アイテムの移動に失敗しました');
        }
    }
    
    /**
     * イベントリスナーのセットアップ
     */
    setupEventListeners() {
        // 削除ボタン
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.delete-item-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.delete-item-btn');
                const itemId = btn.dataset.itemId;
                const itemTitle = btn.dataset.itemTitle;
                
                this.confirmDelete(itemId, itemTitle);
            }
        });
        
        // 展開/折りたたみ
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.toggle-children-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.toggle-children-btn');
                const item = btn.closest('.menu-item');
                
                this.toggleChildren(item);
            }
        });
    }
    
    /**
     * 子アイテムの展開/折りたたみ
     */
    toggleChildren(item) {
        const children = item.querySelector('.menu-item-children');
        if (!children) return;
        
        const btn = item.querySelector('.toggle-children-btn');
        const icon = btn.querySelector('svg');
        
        if (children.classList.contains('hidden')) {
            children.classList.remove('hidden');
            icon.style.transform = 'rotate(90deg)';
        } else {
            children.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }
    
    /**
     * 削除確認
     */
    confirmDelete(itemId, itemTitle) {
        if (!confirm(`「${itemTitle}」を削除しますか？\n\n子アイテムも含めて削除されます。`)) {
            return;
        }
        
        // 削除フォームを送信
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/menu-items/${itemId}`;
        
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
     * 成功メッセージを表示
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    /**
     * エラーメッセージを表示
     */
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    /**
     * 通知を表示
     */
    showNotification(message, type = 'info') {
        // Alpine.jsのグローバル通知を使用する場合
        if (window.Alpine && window.Alpine.store('notifications')) {
            window.Alpine.store('notifications').add(message, type);
            return;
        }
        
        // フォールバック: シンプルなアラート
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// グローバルに公開
window.MenuEditor = MenuEditor;
