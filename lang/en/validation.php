<?php

/**
 * This file is part of Dixlase Menu.
 *
 * Copyright (C) 2025 exc-D inc.
 * https://exc-d.com
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
    'max_menu_depth_required' => 'Maximum menu depth is required',
    'max_menu_depth_integer' => 'Maximum menu depth must be an integer',
    'max_menu_depth_min' => 'Maximum menu depth must be at least 1',
    'max_menu_depth_max' => 'Maximum menu depth must not exceed 10',
    'cache_duration_required' => 'Cache duration is required',
    'cache_duration_integer' => 'Cache duration must be an integer',
    'cache_duration_min' => 'Cache duration must be at least 60 seconds',
    'cache_duration_max' => 'Cache duration must not exceed 86400 seconds',
    'default_target_required' => 'Default target is required',
    'default_target_invalid' => 'Invalid default target value',
    'location_key_required' => 'Location key is required',
    'location_label_required' => 'Location label is required',
];
