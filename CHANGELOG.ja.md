# 変更履歴

Dixlase Menus プラグインの主要な変更はすべてこのファイルに記録します。

フォーマットは [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) に準拠し、
本プラグインはセマンティックバージョニングに従います。

## [0.1.1] — 2026-10-01

### 変更

- ビルドツール: `vite` を 5 から 8.3.1 に更新し、`esbuild` と `postcss`(8.5.28)も
  あわせて更新(#30)。これらのパッケージに出ていた Dependabot の勧告を解消した。
  どれも開発サーバと画面用ファイルのビルドだけに関わるもので、サイトに配布される
  ものには含まれない。リリース ZIP のビルド済みファイルは同じビルドで作られ、
  ハッシュ付きのファイル名だけが変わる。

## [0.1.0] — 2026-10-01
初回リリース。Dixlase `^0.1.0`（Plugin API `^0.1`）、PHP `>= 8.3` が必要です。

### 追加

- 管理 UI によるメニュー管理 — ナビゲーションメニューの構築と並べ替え。
- メニュー項目は、コアの `Linkable` プロバイダ機構を介して、登録済みの任意の
  リンクソース（ページ、法的ページ、カスタム URL）へリンク可能。
- 他のプラグイン・テーマが利用できるよう、`MenuRepositoryInterface`、
  `MenuItemRepositoryInterface`、`MenuSettingRepositoryInterface`、
  `MenuLinkSource` の各契約を公開。
