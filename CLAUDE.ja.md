# Dixlase Menus - Dixlase CMS プラグイン

> **種別**: Dixlase プラグイン | **カテゴリ**: navigation | **バージョン**: 1.0.0
> **名前空間**: `Plugins\DixlaseMenus`

## プラグイン概要

メニュー管理機能を提供するプラグインです。

## アーキテクチャ

**Dixlase CMS** (Laravel 12) のプラグイン。コアアプリケーションはこのプラグインディレクトリから `../../` に配置。

### 主要パス
- **コアルート**: `../../` （`vendor/`, `artisan`, コア `app/` を含む）
- **このプラグイン**: `plugins/DixlaseMenus/`
- **Artisan コマンド**: `docker exec -i dixlase-laravel.test-1 php artisan <command>`

### プラグイン提供機能
- admin_menu
- settings_page

## 開発ルール

### コアとの連携
- **コア内部を直接インポートしない** — `App\Contracts\*` インターフェースを使用
- **全てServiceProviderで登録** — ルート、ビュー、設定、マイグレーション
- **名前空間の分離** — 全クラスは `Plugins\DixlaseMenus\*` 配下
- **マイグレーションは自己完結** — プラグイン独自のテーブルを管理

### PHP 標準
- PHP 8.3, Laravel 12, Livewire 4
- コンストラクタプロパティプロモーションを使用
- 全メソッドに明示的な戻り値型を宣言
- バリデーションは Form Request クラスで（インライン不可）
- インラインコメントよりPHPDocブロックを優先
- Enum キーは TitleCase
- `env()` は直接使わず `config()` を使用

### 翻訳
- 翻訳ファイルは `en/` と `ja/` の両方を必ず用意する

### ルート命名規則
- パターン: `plugin.{slug}.{resource}.{action}`
- ミドルウェアグループ: `plugin`, `plugin.web`, `plugin.admin`

### テスト
- PHPUnit でフィーチャーテストを記述（Pest不可）
- モデルファクトリを使用（手動セットアップ前に既存のstateを確認）
- テスト実行: `docker exec -i dixlase-laravel.test-1 php artisan test plugins/DixlaseMenus/tests/`
- 特定テスト: `docker exec -i dixlase-laravel.test-1 php artisan test --filter=testMethodName`

### コードフォーマット
- Pint はフック経由で編集後に自動実行


## MCP ツール (Laravel Boost)
- `search-docs`: Laravel エコシステムのドキュメント検索
- `tinker`: PHP コードのデバッグ
- `database-query`: 読み取り専用データベースクエリ
- `list-artisan-commands`: Artisan コマンド実行前に利用可能なコマンドを確認