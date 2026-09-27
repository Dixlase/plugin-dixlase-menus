# 変更履歴

Dixlase Menus プラグインの主要な変更はすべてこのファイルに記録します。

フォーマットは [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) に準拠し、
本プラグインはセマンティックバージョニングに従います。

## [0.1.0] — 2026-10-01
初回リリース。Dixlase `^0.1.0`（Plugin API `^0.1`）、PHP `>= 8.3` が必要です。

### 追加

- 管理 UI によるメニュー管理 — ナビゲーションメニューの構築と並べ替え。
- メニュー項目は、コアの `Linkable` プロバイダ機構を介して、登録済みの任意の
  リンクソース（ページ、法的ページ、カスタム URL）へリンク可能。
- 他のプラグイン・テーマが利用できるよう、`MenuRepositoryInterface`、
  `MenuItemRepositoryInterface`、`MenuSettingRepositoryInterface`、
  `MenuLinkSource` の各契約を公開。
