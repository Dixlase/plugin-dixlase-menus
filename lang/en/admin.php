<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc.
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
    // Enums
    'enums' => [
        'menu_location' => [
            'header' => 'Header',
            'footer' => 'Footer',
            'sidebar' => 'Sidebar',
        ],
        'placement_type' => [
            'manual' => 'Manual (Blade/Shortcode)',
            'auto' => 'Auto (by location)',
            'manual_description' => 'Place in templates using @menu directive or [menu] shortcode',
            'auto_description' => 'Automatically display at the selected location',
        ],
    ],

    // Menu List
    'menus' => [
        'index' => [
            'heading' => 'Menu List',
            'create_menu' => 'Create Menu',
            'clear_cache' => 'Clear Cache',
            'no_menus' => 'No menus found',
            'no_menus_description' => 'Get started by creating your first menu',
            'create_first_menu' => 'Create First Menu',
            'table' => [
                'name' => 'Menu Name',
                'location' => 'Location',
                'items' => 'Items',
                'items_count' => 'items',
                'status' => 'Status',
                'actions' => 'Actions',
                'no_location' => 'Not Set',
            ],
        ],
        'create' => [
            'heading' => 'Create Menu',
            'basic_info' => 'Basic Information',
            'name' => 'Menu Name',
            'name_help' => 'Enter a name to identify this menu',
            'slug' => 'Slug',
            'slug_help' => 'Identifier used in URLs and code (lowercase letters, numbers, hyphens, and underscores only)',
            'description' => 'Description',
            'description_help' => 'Menu description (visible only in admin panel)',
            'language' => 'Menu Language',
            'language_help' => 'The language the menu item labels are written in. Translations for other languages are managed from the Multilingual translation manager.',
            'display_settings' => 'Display Settings',
            'placement_type' => 'Placement Type',
            'location' => 'Location',
            'location_help' => 'Select where this menu should be displayed',
            'select_location' => 'Select Location',
            'display_order' => 'Display Order',
            'display_order_help' => 'Display order when multiple menus are in the same location (lower numbers appear first)',
            'is_active' => 'Activate Menu',
            'is_active_help' => 'Uncheck to hide this menu from the site',
            'confirm_title' => 'Create Menu',
            'confirm_message' => 'Are you sure you want to create this menu?',
            'manual_create_hint' => 'After saving, the Blade directive and shortcode will be displayed on the edit page.',
        ],
        'edit' => [
            'heading' => 'Edit Menu',
            'basic_info' => 'Basic Information',
            'name' => 'Menu Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'language' => 'Menu Language',
            'language_help' => 'The language the menu item labels are written in. Translations for other languages are managed from the Multilingual translation manager.',
            'display_settings' => 'Display Settings',
            'placement_type' => 'Placement Type',
            'location' => 'Location',
            'select_location' => 'Select Location',
            'display_order' => 'Display Order',
            'is_active' => 'Activate Menu',
            'placement_code' => 'Placement Code',
            'blade_directive' => 'Blade Directive',
            'shortcode' => 'Shortcode',
            'menu_items' => 'Menu Items',
            'add_item' => 'Add Item',
            'add_items' => 'Add Items',
            'custom_url' => 'Custom URL',
            'search_items' => 'Search...',
            'add_to_menu' => 'Add to Menu',
            'no_source_items' => 'No items found',
            'loading_items' => 'Loading...',
            'custom_url_label' => 'Link text',
            'custom_url_url' => 'https://example.com or /about',
            'no_items' => 'No menu items',
            'no_items_description' => 'Get started by adding your first menu item',
            'add_first_item' => 'Add First Item',
            'children' => 'children',
            'order' => 'Order',
            'add_child' => 'Add Child Item',
            'add_child_hint' => '— adding as child item',
            'published_only_hint' => 'Only published pages are shown.',
            'menu_group' => 'Menu Group',
            'menu_group_label' => 'Group name',
            'menu_group_label_placeholder' => 'e.g. Products, Services',
            'menu_group_hint' => 'Menu groups are label-only items with no link. Use them as parent containers for dropdown or mega menus.',
            'menu_group_badge' => 'Group',
            'icon_pick' => 'Pick icon',
            'icon_picker' => [
                'title' => 'Pick an icon',
                'search_placeholder' => 'Search icons (e.g. home, user, cart)',
                'style_all' => 'All',
                'style_solid' => 'Solid',
                'style_regular' => 'Regular',
                'style_brands' => 'Brands',
                'category_all' => 'All categories',
                'results' => ':total results',
                'results_capped' => 'Showing :start–:end of :total',
                'page_indicator' => 'Page :current of :total',
                'prev_page' => 'Previous',
                'next_page' => 'Next',
                'loading' => 'Loading icons...',
                'no_results' => 'No icons match your search.',
                'clear' => 'Remove icon',
            ],
            'toggle_children' => 'Toggle Children',
            'confirm_title' => 'Save Menu',
            'confirm_message' => 'Are you sure you want to save the changes to this menu?',
            'sidebar_open' => 'Open sidebar',
            'sidebar_close' => 'Close sidebar',
        ],
        'delete_confirm_title' => 'Delete Menu',
        'delete_confirm_message' => 'Are you sure you want to delete this menu? All menu items will also be deleted.',
    ],

    // Menu Items
    'menu_items' => [
        'create' => [
            'heading' => 'Add Menu Item',
            'parent_item' => 'Parent Item',
            'basic_info' => 'Basic Information',
            'title' => 'Title',
            'title_help' => 'Text displayed in the menu',
            'link_settings' => 'Link Settings',
            'source_type' => 'Link Type',
            'url' => 'URL',
            'url_help' => 'Full URL or relative path (e.g., https://example.com or /about)',
            'source_id' => 'Source ID',
            'source_id_help' => 'ID of the linked page or category',
            'target' => 'Link Target',
            'target_default' => 'Use Default Setting',
            'display_settings' => 'Display Settings',
            'css_class' => 'CSS Class',
            'css_class_help' => 'Custom CSS classes (space-separated)',
            'icon_class' => 'Icon Class',
            'icon_class_help' => 'Icon class such as Font Awesome (e.g., fas fa-home)',
            'display_order' => 'Display Order',
            'is_active' => 'Activate Item',
            'is_active_help' => 'Uncheck to hide this item',
        ],
        'edit' => [
            'heading' => 'Edit Menu Item',
            'basic_info' => 'Basic Information',
            'title' => 'Title',
            'link_settings' => 'Link Settings',
            'source_type' => 'Link Type',
            'url' => 'URL',
            'source_id' => 'Source ID',
            'target' => 'Link Target',
            'target_default' => 'Use Default Setting',
            'display_settings' => 'Display Settings',
            'css_class' => 'CSS Class',
            'icon_class' => 'Icon Class',
            'display_order' => 'Display Order',
            'is_active' => 'Activate Item',
        ],
    ],

    // Settings
    'settings' => [
        'heading' => 'Menu Settings',

        'basic' => [
            'title' => 'Basic Settings',
            'menu_structure' => 'Menu Structure',
            'max_menu_depth' => 'Maximum Menu Depth',
            'max_menu_depth_help' => 'Set the maximum depth of menu hierarchy (1-10)',
            'default_target' => 'Default Link Target',
            'default_target_help' => 'Set the default link target for menu items',
            'target_self' => 'Same Window (_self)',
            'target_blank' => 'New Window (_blank)',
            'target_parent' => 'Parent Frame (_parent)',
            'target_top' => 'Top Frame (_top)',
        ],

        'cache' => [
            'title' => 'Cache Settings',
            'menu_cache' => 'Menu Cache',
            'enable_menu_cache' => 'Enable Menu Cache',
            'enable_menu_cache_help' => 'Enable menu caching to improve performance',
            'cache_duration' => 'Cache Duration (seconds)',
            'cache_duration_help' => 'Set the cache duration in seconds (60-86400)',
            'clear_cache' => 'Clear Cache',
            'clear_cache_help' => 'Clear all menu caches',
            'clear_cache_confirm' => 'Are you sure you want to clear the cache?',
        ],

        'menu_items' => [
            'title' => 'Menu Items',
            'add_item' => 'Add Menu',
            'add_child' => 'Add Child Menu',
            'label' => 'Label',
            'label_placeholder' => 'Text to display in menu',
            'url' => 'URL',
            'url_placeholder' => 'https://example.com or /about',
            'target' => 'Target',
            'source_type' => 'Link Type',
            'source_custom' => 'Custom URL',
            'source_select' => 'Select Content',
            'select_content' => 'Select content...',
            'no_providers' => 'No content providers available',
            'items_help' => 'Add menu items. Drag and drop to reorder.',
        ],

        'confirm' => [
            'title' => 'Save Settings',
            'message' => 'Are you sure you want to save the menu settings?',
        ],
    ],

    // Controller messages
    'messages' => [
        // Menu
        'menu_created' => 'Menu created successfully',
        'menu_updated' => 'Menu updated successfully',
        'menu_deleted' => 'Menu deleted successfully',
        'menu_restored' => 'Menu restored successfully',
        'menu_not_found' => 'Menu not found',
        'slug_already_exists' => 'This slug is already in use',

        // Menu item
        'menu_item_created' => 'Menu item created successfully',
        'menu_item_updated' => 'Menu item updated successfully',
        'menu_item_deleted' => 'Menu item deleted successfully',
        'menu_item_moved' => 'Menu item moved successfully',
        'menu_item_not_found' => 'Menu item not found',
        'max_depth_exceeded' => 'Maximum depth exceeded',
        'cannot_set_self_as_parent' => 'Cannot set self as parent',
        'cannot_set_descendant_as_parent' => 'Cannot set descendant as parent',

        // Order
        'order_updated' => 'Order updated successfully',
        'order_update_failed' => 'Failed to update order',
        'no_items_to_update' => 'No items to update',
        'move_failed' => 'Failed to move item',

        // Settings
        'settings_updated' => 'Settings updated successfully',
        'cache_cleared' => 'Cache cleared successfully',

        // Bulk save
        'menu_items_saved' => 'Menu items saved successfully',
        'menu_items_save_failed' => 'Failed to save menu items',
    ],
];
