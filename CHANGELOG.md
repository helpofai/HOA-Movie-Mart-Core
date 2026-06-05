# Changelog

All notable changes to the HOA Movie Mart Core plugin.

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
