# Dixlase Menus

For Japanese, see [README.ja.md](./README.ja.md).

Menu management for Dixlase: multiple named menus per location and language, a drag-and-drop hierarchy editor up to a configurable depth, pluggable link sources so other plugins can surface their content as link candidates, label-only menu-group nodes for dropdown / mega menus, and a Font Awesome icon picker.

## Features

- **Multiple menus per location / language** — Each menu is identified by slug + language and assigned to a location string (`header`, `footer`, `sidebar`, or any custom value). Slug uniqueness is scoped to `(slug, language, deleted_at)`, so the same slug can coexist across languages and survive soft-delete + recreate.
- **Drag-and-drop hierarchy editor** — Configurable max depth (default 5). Parent / child / grandchild items are reordered by drag, with a revert-then-replace SortableJS pattern that keeps DOM moves consistent with Alpine.js state.
- **Manual or auto placement** — Each menu's `placement_type` is either `manual` (operator edits the tree directly) or `auto` (the tree is supplied by a registered placement provider, e.g. an all-published-pages list).
- **Pluggable link sources** — Other plugins register an implementation of the `MenuLinkSource` contract to expose their content (pages, posts, categories, …) as link candidates in the "Add item" picker. Selecting one stores `(source_type, source_id)`; the public URL is composed on read so renames on the source side stay transparent to the menu.
- **Menu groups** — Add label-only items with no URL to act as dropdown or mega-menu parents. Available when the target depth has room for children.
- **Icon picker (Font Awesome Free)** — Search the full ~1,895-icon set, filter by style (Solid / Regular / Brands) and category, paginated 200 per page. Icons are stored as an *opaque icon reference*; future schemes (`media:<id>`, `url:<href>`) will share the same column without a schema change.
- **Custom URL items** — Free-text label + URL + target (`_self` / `_blank`) for ad-hoc links that aren't backed by a content source.
- **Theme integration via DTO** — Public-side consumers (themes) receive a `MenuItemDTO` graph rather than raw Eloquent models. The DTO carries the resolved label, composed URL, icon reference, and children, keeping the render contract stable across schema changes.

## Installation

Open the admin panel under **Dashboard → Plugins**, find this plugin, then download and enable it. The plugin's tables are created automatically on enable.

## Usage

After enable, **Dashboard → Menus** appears in the admin sidebar.

- **List view** shows every menu with its slug, location, language, and active state.
- **New menu** sets the menu's identity (name, slug, location, language) and its placement type.
- **Edit** opens the menu editor with a left-side item tree (drag to reorder, click to expand children) and a right-side detail panel. **+ Add item** launches a tabbed picker offering each registered link source, a custom URL, or a menu group.
- **Icon picker** is opened from each item's icon button. Search across all ~1,895 FA Free icons; the result list paginates 200 per page.

Themes read menus through `MenuItemDTO`; the DTO carries the resolved label, composed URL, icon reference, and children.

## Capabilities

The plugin consumes one contract that other plugins can implement:

- **`MenuLinkSource`** — Defined in `plugins/DixlaseMenus/app/Contracts/`. Other plugins implement this contract to make their content selectable in the menu editor's "Add item" picker. DixlasePages, for example, exposes its pages through its `linkable` capability; the menu plugin's `LinkableProviderAdapter` then adapts that capability to `MenuLinkSource` automatically.

## License

Dixlase Menus is distributed under a **dual license**:

- **Open Source License**: [GNU General Public License v3](./LICENSE)
- **Commercial License**: A separate commercial license is planned for use cases where GPL v3 compliance is not feasible. **It is not yet available** — only a draft of the eventual terms is present in [LICENSE.commercial](./LICENSE.commercial). For availability timing or other questions, contact **info@dixlase.org**.

A short overview of how these files fit together is in [NOTICE](./NOTICE) ([日本語](./NOTICE.ja)).

## Contributing

The Contributor License Agreement (CLA) is still under review, so code Pull Requests are not being accepted at this time. Once the CLA is finalized, contributions will open under the [Dixlase Copyright Policy](https://github.com/Dixlase/dixlase-core/blob/main/COPYRIGHT-POLICY.md) and the Dixlase CLA (see CONTRIBUTING.md). Bug reports and proposals via Issues are welcome in the meantime.

---
(C) exc-D inc. - 2026
