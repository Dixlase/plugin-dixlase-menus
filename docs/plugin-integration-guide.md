# メニュープラグイン連携ガイド

このドキュメントでは、DixlaseMenusプラグインと他のプラグイン（例：DixlasePagesプラグイン）を連携させ、メニューアイテムを自動的に取得する方法を説明します。

## 目次

1. [概要](#概要)
2. [コアのContract/DTO](#コアのcontractdto)
3. [実装例：DixlasePagesプラグイン](#実装例dixlasepagesプラグイン)
4. [サービスプロバイダーへの登録](#サービスプロバイダーへの登録)
5. [メニュー管理画面での使用](#メニュー管理画面での使用)

---

## 概要

Dixlaseでは、プラグイン間の連携を実現するために、コアに以下のContract/DTOが用意されています：

| ファイル | 説明 |
|---------|------|
| `app/Contracts/PluginIntegration/LinkableInterface.php` | リンク可能なコンテンツの最小契約 |
| `app/Contracts/PluginIntegration/LinkableProviderInterface.php` | コンテンツプロバイダーの契約 |
| `app/DTO/PluginIntegration/LinkableDTO.php` | リンク可能なコンテンツのDTO |
| `app/DTO/PluginIntegration/MenuItemDTO.php` | メニューアイテムのDTO |

これらを使用することで：

- プラグインが管理するコンテンツ（ページ、投稿、カテゴリなど）をメニューアイテムとして提供
- 手動でURLを入力する代わりに、既存のコンテンツから選択してメニューを作成
- コンテンツのタイトルとURLを自動的に取得
- ターゲット属性（`_self`, `_blank`など）の指定

---

## コアのContract/DTO

### LinkableInterface

リンク可能なコンテンツの最小契約です。

```php
interface LinkableInterface
{
    public function getId(): string;
    public function getTitle(): string;
    public function getUrl(): string;
    public function getType(): string;        // 'post', 'page', 'media', etc.
    public function getSource(): string;      // 'core' or plugin slug
    public function getSourceTable(): ?string;
}
```

### LinkableProviderInterface

コンテンツを提供するプラグインが実装する契約です。

```php
interface LinkableProviderInterface
{
    public function getProviderKey(): string;           // 'dixlase-pages'
    public function getProviderLabel(): string;        // 'ページ'
    public function getProviderIcon(): ?string;        // 'fas fa-file-alt'
    public function isAvailable(): bool;
    public function getAvailableItems(int $limit = 100): array;
    public function searchItems(string $query, int $limit = 20): array;
    public function getItemById(string $id): ?LinkableDTO;
}
```

### LinkableDTO

リンク可能なコンテンツのデータ転送オブジェクトです。

```php
final readonly class LinkableDTO implements LinkableInterface, JsonSerializable
{
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public string $type,
        public string $source,
        public ?string $sourceTable = null,
        public ?string $locale = null,
        public array $meta = [],
    ) {}
}
```

### MenuItemDTO

メニューアイテムのデータ転送オブジェクトです。

```php
final readonly class MenuItemDTO implements JsonSerializable
{
    public function __construct(
        public string $label,
        public string $url,
        public string $target = '_self',
        public ?string $sourceType = 'custom',
        public ?string $sourceId = null,
        public ?string $sourceProvider = null,
        public ?string $iconClass = null,
        public ?string $cssClass = null,
        public int $displayOrder = 0,
        public bool $isActive = true,
        public array $meta = [],
    ) {}
    
    // LinkableDTOから生成
    public static function fromLinkable(LinkableDTO $linkable, string $target = '_self'): self;
}
```

---

## 実装例：DixlasePagesプラグイン

### 1. LinkableProviderInterfaceの実装

**場所**: `plugins/DixlasePages/app/Services/PageLinkableProvider.php`

```php
<?php

namespace Plugins\DixlasePages\App\Services;

use App\Contracts\PluginIntegration\LinkableProviderInterface;
use App\DTO\PluginIntegration\LinkableDTO;
use Plugins\DixlasePages\App\Models\Page;

class PageLinkableProvider implements LinkableProviderInterface
{
    public function getProviderKey(): string
    {
        return 'dixlase-pages';
    }
    
    public function getProviderLabel(): string
    {
        return __('dixlase-pages::admin.provider.label');
    }
    
    public function getProviderIcon(): ?string
    {
        return 'fas fa-file-alt';
    }
    
    public function isAvailable(): bool
    {
        return class_exists(Page::class);
    }
    
    public function getAvailableItems(int $limit = 100): array
    {
        $pages = Page::where('status', 'published')
            ->orderBy('title')
            ->limit($limit)
            ->get();
        
        return $pages->map(fn($page) => $this->pageToDTO($page))->toArray();
    }
    
    public function searchItems(string $query, int $limit = 20): array
    {
        $pages = Page::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();
        
        return $pages->map(fn($page) => $this->pageToDTO($page))->toArray();
    }
    
    public function getItemById(string $id): ?LinkableDTO
    {
        $page = Page::find($id);
        
        return $page ? $this->pageToDTO($page) : null;
    }
    
    protected function pageToDTO(Page $page): LinkableDTO
    {
        return new LinkableDTO(
            id: (string) $page->id,
            title: $page->title,
            url: route('pages.show', ['slug' => $page->slug]),
            type: 'page',
            source: 'dixlase-pages',
            sourceTable: 'pages',
        );
    }
}
```

---

## サービスプロバイダーへの登録

### プラグイン側（DixlasePages）

**場所**: `plugins/DixlasePages/app/Providers/DixlasePagesServiceProvider.php`

```php
<?php

namespace Plugins\DixlasePages\App\Providers;

use Illuminate\Support\ServiceProvider;
use Plugins\DixlasePages\App\Services\PageLinkableProvider;

class DixlasePagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // LinkableProviderを登録
        $this->app->singleton(PageLinkableProvider::class);
        
        // タグ付けして、メニュープラグインから取得できるようにする
        $this->app->tag([PageLinkableProvider::class], 'linkable.providers');
    }
}
```

---

## メニュー管理画面での使用

### コントローラーでの取得

**場所**: `plugins/DixlaseMenus/app/Http/Controllers/Admin/MenuSettingsController.php`

```php
<?php

namespace Plugins\DixlaseMenus\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\PluginIntegration\LinkableProviderInterface;

class MenuSettingsController extends Controller
{
    public function index()
    {
        // 登録されているすべてのLinkableProviderを取得
        $linkableProviders = [];
        
        try {
            $providers = app()->tagged('linkable.providers');
            
            foreach ($providers as $provider) {
                if ($provider instanceof LinkableProviderInterface && $provider->isAvailable()) {
                    $items = $provider->getAvailableItems();
                    
                    $linkableProviders[] = [
                        'key' => $provider->getProviderKey(),
                        'label' => $provider->getProviderLabel(),
                        'icon' => $provider->getProviderIcon(),
                        'items' => array_map(fn($item) => $item->toArray(), $items),
                    ];
                }
            }
        } catch (\Exception $e) {
            // プロバイダーが登録されていない場合は空配列
        }
        
        return view('dixlase-menus::admin.settings.menus.index', [
            'settings' => $this->getSettings(),
            'linkableProviders' => $linkableProviders,
        ]);
    }
}
```

---

## 利点

1. **疎結合**: プラグイン間の依存関係を最小限に
2. **拡張性**: 新しいプラグインが追加されても、同じパターンで簡単に統合
3. **型安全性**: DTOを使用することで、データの整合性を保証
4. **再利用性**: コアのContract/DTOを使用することで、コードの重複を削減

## 実装状況

1. ✅ DixlasePagesプラグインに`PageLinkableProvider`を実装
2. ✅ DixlasePagesのServiceProviderで`linkable.providers`タグを登録
3. ✅ メニュープラグインのコントローラーでプロバイダーを取得
4. 他のプラグイン（ブログ、カテゴリなど）にも同様のパターンを適用

## メニュー機能

- **ドラッグ＆ドロップ**: SortableJSを使用して親メニューの順序を変更可能
- **階層メニュー**: 各親メニューに最大3つの子メニューを追加可能
- **コンテンツ選択**: 他のプラグインのコンテンツ（ページなど）をGUIで選択可能
- **ターゲット属性**: 各メニューアイテムにリンクターゲット（_self, _blank等）を設定可能
