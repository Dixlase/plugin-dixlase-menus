<?php

/**
 * This file is part of the Dixlase Menus plugin.
 *
 * Copyright (C) 2026 exc-D inc. and Dixlase contributors
 * https://exc-d.com
 */

namespace Plugins\DixlaseMenus\Tests\Unit;

use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Plugins\DixlaseMenus\App\Services\MenuService;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Menu item URLs land in `href="{{ $url }}"` on every front-end page. Blade
 * escapes the HTML but not the scheme, so a stored `javascript:` URL executes
 * for every visitor who clicks the item. Nothing checked the scheme: the
 * plugin's own validateUrl() helper was never called from anywhere, and would
 * not have caught it anyway -- it delegates to FILTER_VALIDATE_URL, which
 * accepts `javascript://%0aalert(1)`.
 *
 * The guard lives in MenuService::saveItem() because the menu editor saves
 * through /sync, which reads raw request input and never builds a Form
 * Request. Validation rules alone would have missed the only path that
 * matters.
 */
class MenuUrlSchemeGuardTest extends TestCase
{
    private function guard(?string $url): ?string
    {
        // Built without the constructor on purpose: the guard is pure string
        // handling and touches none of the repositories MenuService injects,
        // so resolving them here would only couple the test to wiring that has
        // nothing to do with what is being verified.
        $service = (new ReflectionClass(MenuService::class))->newInstanceWithoutConstructor();

        $method = new ReflectionMethod($service, 'assertAllowedUrlScheme');
        $method->setAccessible(true);

        return $method->invoke($service, $url);
    }

    /**
     * @return list<array{0: string}>
     */
    public static function dangerousUrls(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'mixed case' => ['JavaScript:alert(1)'],
            // Browsers ignore control characters inside a scheme, so these
            // navigate exactly like javascript: does.
            'tab in scheme' => ["java\tscript:alert(1)"],
            'newline in scheme' => ["java\nscript:alert(1)"],
            'leading whitespace' => ['  javascript:alert(1)'],
            'data uri' => ['data:text/html,<script>alert(1)</script>'],
            'vbscript' => ['vbscript:msgbox(1)'],
            // Carries no scheme, but is not site-relative either: it inherits
            // the page scheme and leaves the origin.
            'protocol relative' => ['//evil.example.com'],
        ];
    }

    #[DataProvider('dangerousUrls')]
    public function test_dangerous_schemes_are_refused(string $url): void
    {
        $this->expectException(ValidationException::class);
        $this->guard($url);
    }

    /**
     * @return list<array{0: string}>
     */
    public static function legitimateUrls(): array
    {
        return [
            'root relative' => ['/about'],
            'nested path' => ['/news/2026/release'],
            'fragment' => ['#top'],
            'query only' => ['?page=2'],
            'relative path' => ['contact'],
            'https' => ['https://example.com/path?a=1#b'],
            'http' => ['http://example.com'],
            'mailto' => ['mailto:someone@example.com'],
            'tel' => ['tel:+81312345678'],
        ];
    }

    #[DataProvider('legitimateUrls')]
    public function test_ordinary_links_still_save(string $url): void
    {
        $this->assertSame(
            $url,
            $this->guard($url),
            'The guard must not reject links operators legitimately use. A fix that blocks normal work is not a fix.'
        );
    }

    public function test_empty_values_pass_through_untouched(): void
    {
        $this->assertSame('', $this->guard(''));
        $this->assertNull($this->guard(null));
    }

    /**
     * Menu groups have no URL at all; the guard must not turn that into a
     * validation error.
     */
    public function test_whitespace_only_is_not_treated_as_a_scheme(): void
    {
        $this->assertSame('   ', $this->guard('   '));
    }

    /**
     * The classic item form (items.store / items.update) goes through Form
     * Requests, not /sync, and validated the URL only as a string -- so a
     * `javascript:` URL saved through it was rendered into every page.
     *
     * @return array<string, array{0: class-string}>
     */
    public static function itemFormRequests(): array
    {
        return [
            'store' => [\Plugins\DixlaseMenus\App\Http\Requests\AdminMenuItemStoreRequest::class],
            'update' => [\Plugins\DixlaseMenus\App\Http\Requests\AdminMenuItemUpdateRequest::class],
        ];
    }

    #[DataProvider('itemFormRequests')]
    public function test_the_item_form_requests_refuse_dangerous_urls(string $requestClass): void
    {
        $rule = (new $requestClass())->rules()['url'];

        foreach (self::dangerousUrls() as $label => [$url]) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Validator::make(['url' => $url], ['url' => $rule])->fails(),
                "{$requestClass} accepted {$label}: {$url}"
            );
        }

        foreach (self::legitimateUrls() as [$url]) {
            $this->assertFalse(
                \Illuminate\Support\Facades\Validator::make(['url' => $url], ['url' => $rule])->fails(),
                "{$requestClass} rejected an ordinary link: {$url}"
            );
        }
    }
}
