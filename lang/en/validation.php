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

return [
    // Menu
    'name_required' => 'Menu name is required',
    'name_max' => 'Menu name must not exceed 255 characters',
    'slug_required' => 'Slug is required',
    'slug_max' => 'Slug must not exceed 255 characters',
    'slug_format' => 'Slug must contain only lowercase letters, numbers, hyphens, and underscores',
    'location_max' => 'Location must not exceed 255 characters',
    'description_max' => 'Description must not exceed 1000 characters',
    'display_order_integer' => 'Display order must be an integer',
    'display_order_min' => 'Display order must be at least 0',

    // Menu item
    'parent_not_found' => 'Parent item not found',
    'title_required' => 'Title is required',
    'title_max' => 'Title must not exceed 255 characters',
    'source_type_required' => 'Link source type is required',
    'url_max' => 'URL must not exceed 2048 characters',
    'target_invalid' => 'Invalid target value',
    'css_class_max' => 'CSS class must not exceed 255 characters',
    'icon_class_max' => 'Icon class must not exceed 255 characters',
    'visibility_condition_json' => 'Visibility condition must be valid JSON',

    // Settings
    'default_target_invalid' => 'Invalid default target value',
    'menu_label_required' => 'Menu label is required',
    'menu_label_max' => 'Menu label must not exceed 255 characters',
    'menu_url_max' => 'URL must not exceed 2048 characters',
    'menu_target_invalid' => 'Invalid target value',
    'menu_url_scheme_not_allowed' => 'This link uses a scheme that is not allowed. Use http, https, mailto, tel, or a path beginning with /.',
];
