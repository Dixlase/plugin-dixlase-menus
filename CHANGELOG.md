# Changelog

All notable changes to the Dixlase Menus plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this plugin follows Semantic Versioning.

## [0.1.7] — 2026-10-24

### Development

- Bump `vite` from 8.3.1 to 8.3.3, a build-time dependency that is not shipped to sites (#48).
  No runtime code changed.

## [0.1.6] — 2026-10-18

### Changed

- Comments, docblocks and the documentation no longer name plugins that are not distributed publicly. Each integration is
  described by the core mechanism it goes through (#47, fixes #46). No behaviour changed.

## [0.1.5] — 2026-10-17

### Fixed

- The icon picker on the menu edit screen shows its icons in a grid again, instead of one
  column of full-width cells. `plugin.json` now declares `tailwind_content`, so the site's
  Tailwind build scans the plugin's views and keeps the grid classes they use (#44, fixes #43).
  The classes reach a site's CSS at the next core Tailwind build after the plugin sources are
  regenerated (`dls:tailwind:regenerate-plugin-sources`).

## [0.1.4] — 2026-10-16

### Development

- A self-audit test replays the admin plugin audit's own checks: it fails when a
  permission declared in `plugin.json` does not match the code, or when a view breaks
  the CSP strict mode (#42, fixes #41). No runtime code changed.

## [0.1.3] — 2026-10-13

### Fixed

- Opening the screen to add or edit a menu item no longer fails with a server error. The
  screen called methods that do not exist on the link-source manager and the link
  sources; it now uses the real ones, and offers only the link sources that are
  available (#39, fixes #38).

### Development

- A smoke test opens every admin GET route of the plugin as the admin and the editor
  role and checks that none returns a server error (#40, fixes #37).

## [0.1.2] — 2026-10-12

### Development

- The built front-end assets (`resources/assets/`) are no longer tracked in git (#33,
  fixes #32). Run `npm run build` in a local checkout; deployments and the release ZIP
  build the assets as before. The assets were never part of the signature.
- The test workflow no longer sets the unused `COMPOSER_AUTH` value (#35, fixes #34), and
  checks out core with its default token instead of `CORE_REPO_TOKEN`, so Dependabot pull
  requests can run their tests (#36, fixes Dixlase/dixlase-core#505).

## [0.1.1] — 2026-10-01

### Changed

- Build tooling: `vite` 5 → 8.3.1, with `esbuild` and `postcss` 8.5.28 updated
  alongside (#30). This clears the Dependabot advisories for those packages,
  all of which affect only the development server and the asset build — nothing in
  them is shipped to sites. The prebuilt assets in the release ZIP are produced by
  the same build as before; only their hashed file names change.

## [0.1.0] — 2026-10-01
Initial release. Requires Dixlase `^0.1.0` (Plugin API `^0.1`), PHP `>= 8.3`.

### Added

- Menu management with an admin UI — build and order navigation menus.
- Menu items can link to any registered link source (pages, legal pages, custom
  URLs) via the core `Linkable` provider mechanism.
- Publishes the `MenuRepositoryInterface`, `MenuItemRepositoryInterface`,
  `MenuSettingRepositoryInterface`, and `MenuLinkSource` contracts for other
  plugins and themes to consume.
