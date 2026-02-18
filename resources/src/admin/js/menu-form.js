/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
 *
 * メニュー作成・編集フォーム（スラッグ自動生成・バリデーション）
 */

class MenuForm {
    /**
     * @param {Object} options
     */
    constructor(options = {}) {
        this.form = options.form || document.getElementById('menu-form');
        this.slugInput = options.slugInput || document.getElementById('slug');
        this.nameInput = options.nameInput || document.getElementById('name');
        this.autoGenerateSlug = options.autoGenerateSlug !== false;

        this.init();
    }

    /**
     * 初期化
     */
    init() {
        if (!this.form) {
            return;
        }

        this.setupSlugGeneration();
        this.setupFormValidation();
    }

    /**
     * スラッグ自動生成のセットアップ
     */
    setupSlugGeneration() {
        if (!this.nameInput || !this.slugInput) {
            return;
        }

        let userEditedSlug = this.slugInput.value !== '';

        this.slugInput.addEventListener('input', () => {
            userEditedSlug = true;
        });

        this.nameInput.addEventListener('input', () => {
            if (this.autoGenerateSlug && !userEditedSlug) {
                this.slugInput.value = this.generateSlug(this.nameInput.value);
            }
        });
    }

    /**
     * スラッグを生成
     */
    generateSlug(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/[\u3040-\u309F\u30A0-\u30FF\u4E00-\u9FFF]/g, '')
            .replace(/[^a-z0-9-_\s]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    /**
     * フォームバリデーションのセットアップ
     */
    setupFormValidation() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                return false;
            }
        });
    }

    /**
     * フォームをバリデート
     */
    validateForm() {
        let isValid = true;
        const errors = [];

        if (!this.nameInput.value.trim()) {
            errors.push('メニュー名は必須です');
            isValid = false;
        }

        if (!this.slugInput.value.trim()) {
            errors.push('スラッグは必須です');
            isValid = false;
        } else if (!/^[a-z0-9-_]+$/.test(this.slugInput.value)) {
            errors.push('スラッグは小文字の英数字、ハイフン、アンダースコアのみ使用できます');
            isValid = false;
        }

        if (!isValid) {
            alert(errors.join('\n'));
        }

        return isValid;
    }
}

// DOMContentLoaded時に自動初期化
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('menu-form')) {
        new MenuForm();
    }
});
