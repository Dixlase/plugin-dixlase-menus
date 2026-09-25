<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc. and Dixlase contributors
 * https://exc-d.com
 *
 * Dixlase Menus is dual-licensed. You may use this file under either:
 *
 *   (a) the GNU General Public License version 3 or later, as published
 *       by the Free Software Foundation; or
 *
 *   (b) a commercial license agreement obtained from exc-D inc.
 *
 * Unless you have entered into a commercial license agreement, this
 * file is governed by the GPL terms below.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Plugins\DixlaseMenus\App\Support;

/**
 * Which menu item URLs may be rendered as a link.
 *
 * Menu URLs end up in `href` on every page, so a `javascript:` (or `data:`,
 * `vbscript:`) URL runs script for every visitor who clicks it. Allowed: a
 * relative path, query or fragment, or an absolute http / https / mailto / tel
 * URL. Refused: any other scheme, and `//host` (no scheme, but it leaves the
 * origin). Control characters and whitespace are stripped before the scheme is
 * read, because browsers ignore them inside one -- `java\tscript:` navigates
 * just like `javascript:`.
 *
 * Shared by the menu editor's /sync path (MenuService) and the item store /
 * update Form Requests, so every way of saving an item applies the same rule.
 */
final class MenuUrlPolicy
{
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function isAllowed(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return true;
        }

        $forSchemeCheck = preg_replace('/[\x00-\x20]/', '', trim($url)) ?? '';

        if (str_starts_with($forSchemeCheck, '//')) {
            return false;
        }

        if (preg_match('/^([A-Za-z][A-Za-z0-9+.\-]*):/', $forSchemeCheck, $matches) !== 1) {
            return true;
        }

        return in_array(strtolower($matches[1]), self::ALLOWED_SCHEMES, true);
    }

    /**
     * A validation rule closure for Form Requests.
     */
    public static function rule(): \Closure
    {
        return static function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value !== null && (! is_string($value) || ! self::isAllowed($value))) {
                $fail(__('dixlase-menus::validation.menu_url_scheme_not_allowed'));
            }
        };
    }
}
