# Dixlase Menus

For Japanese, see [README.ja.md](./README.ja.md).

Menu management for Dixlase: multiple named menus per location and language, a drag-and-drop hierarchy editor up to a configurable depth, pluggable link sources so other plugins can surface their content as link candidates, label-only menu-group nodes for dropdown / mega menus, and a Font Awesome icon picker.

## Features

- **Multiple menus per location / language** — Each menu is keyed by slug + language and assigned to a location (`header`, `footer`, custom).
- **Hierarchy editor** — Configurable max depth (default 5), drag-and-drop reorder for parent / child / grandchild items.
- **Manual or auto placement** — Edit the tree directly, or have it supplied by a registered placement provider.
- **Pluggable link sources** — Other plugins surface their content in the "Add item" picker via the `MenuLinkSource` contract.
- **Menu groups** — Label-only nodes that act as dropdown / mega-menu parents.
- **Icon picker** — ~1,895 Font Awesome Free icons, filterable by style and category, paginated 200 per page.
- **Custom URL items** — Free-text label + URL + target for ad-hoc links.
- **Theme integration** — Themes consume `MenuItemDTO` for a stable render contract across schema changes.

## Installation

Open the admin panel under **Dashboard → Plugins**, find this plugin, then download and enable it. The plugin's tables are created automatically on enable.

## Usage

Once enabled, **Menus** appears in the admin sidebar with list / new / edit screens. The editor shows a draggable item tree on the left and a detail panel on the right; **+ Add item** picks from registered link sources, custom URLs, or menu groups, and each item's icon button opens the Font Awesome picker.

Themes read menus through `MenuItemDTO`, which carries the resolved label, composed URL, icon reference, and children.

## Capabilities

The plugin consumes one contract that other plugins can implement:

- **`MenuLinkSource`** — Other plugins implement this to surface their content in the "Add item" picker. DixlasePages does so via its `linkable` capability through the built-in `LinkableProviderAdapter`.

## License

Dixlase Menus is distributed under a **dual license**:

- **Open Source License**: [GNU General Public License v3](./LICENSE)
- **Commercial License**: A separate commercial license is planned for use cases where GPL v3 compliance is not feasible. **It is not yet available** — only a placeholder is present in [LICENSE-COMMERCIAL](./LICENSE-COMMERCIAL). For availability timing or other questions, contact **info@dixlase.org**.

A short overview of how these files fit together is in [NOTICE](./NOTICE) ([日本語](./NOTICE.ja)).

## Contributing

The Contributor License Agreement (CLA) is still under review, so code Pull Requests are not being accepted at this time. Once the CLA is finalized, contributions will open under the [Dixlase Copyright Policy](https://github.com/Dixlase/dixlase-core/blob/main/COPYRIGHT-POLICY.md) and the Dixlase CLA (see CONTRIBUTING.md). Bug reports and proposals via Issues are welcome in the meantime.

---

(C) exc-D inc.
