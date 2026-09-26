# Changelog

All notable changes to the Dixlase Menus plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this plugin follows Semantic Versioning.

## [0.1.0] — 2026-10-01
Initial release. Requires Dixlase `^0.1.0` (Plugin API `^0.1`), PHP `>= 8.3`.

### Added

- Menu management with an admin UI — build and order navigation menus.
- Menu items can link to any registered link source (pages, legal pages, custom
  URLs) via the core `Linkable` provider mechanism.
- Publishes the `MenuRepositoryInterface`, `MenuItemRepositoryInterface`,
  `MenuSettingRepositoryInterface`, and `MenuLinkSource` contracts for other
  plugins and themes to consume.
