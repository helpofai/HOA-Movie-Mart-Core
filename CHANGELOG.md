# Changelog

All notable changes to the HOA Movie Mart Core plugin.

---

## [2.0.1] — 2026-06-05 — Update test

### Added
- GitHub zipball folder auto-rename via `upgrader_source_selection` filter
- Filesystem writability pre-flight checks

### Fixed
- Plugin updates now install correctly on live servers (GitHub folder wrapping resolved)

---

## [1.9.0] — 2026-06-05

### Fixed
- Plugin updater filesystem initialization and writability checks
- Better error messages with actionable fix commands

---

## [1.8.0] — 2026-06-05

### Changed
- "Update Plugin Now" now opens terminal with live progress during install

---

## [1.7.0] — 2026-06-05

### Added
- One-click "Update Plugin Now" button with Yes/No confirmation dialog

---

## [1.6.0] — 2026-06-05

### Changed
- Switched from Releases API to commit-based update detection (`/commits?per_page=1`)
- Update comparison now uses commit SHA instead of version tags
- SSL verify auto-disabled on localhost environments
- Added `is_local()` helper for environment detection

### Fixed
- cURL SSL errors on WAMP/XAMPP localhost installs

---

## [1.5.0] — 2026-06-05

### Added
- GitHub auto-update system via separate repo (`helpofai/HOA-Movie-Mart-Core`)
- `HOA_Plugin_Updater` class with version comparison, caching, WordPress integration
- Shared GitHub token support with theme settings
- Plugin info popup in WordPress plugin details modal
- CHANGELOG.md and updated README.md

### Changed
- Plugin version bumped to 1.5.0
- README rewritten with install guide, features list, development workflow

---

## [1.4.0] — 2026-03-01

### Added
- DMCA active toggle column in movie admin list
- GitHub auto-update system (`HOA_Plugin_Updater`)
- Shared token support with HOA Movie Mart theme settings

### Changed
- Post type registration hardened with proper capability types
- Download link repeater now encrypts URLs via `hoa_encrypt_url` when available
- Legacy download link fields maintained for backwards compatibility

### Fixed
- Report post type slug corrected from `report` to `hoa_report` to match registration

---

## [1.3.0] — 2025-11-15

### Added
- Movie year taxonomy
- Movie quality taxonomy
- Director and Cast taxonomies
- Request CPT with Telegram admin alerts
- Report CPT for dead link submissions
- Custom widgets: Latest Movies, Recent Posts, Taxonomy List

### Changed
- Download links migrated from legacy `_movie_download_link_720p` / `_movie_download_link_1080p` to JSON-based `_movie_download_links_json`

---

## [1.2.0] — 2025-08-01

### Added
- Meta box for IMDb rating, runtime, trailer URL, language
- Download link repeater in admin meta box
- Telegram share button integration

---

## [1.1.0] — 2025-05-10

### Added
- Genre taxonomy
- Basic movie meta fields

---

## [1.0.0] — 2025-03-01

### Added
- Initial release
- Movie custom post type
- Basic admin UI
