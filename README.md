# HOA Movie Mart Core

**Core plugin for the HOA Movie Mart WordPress theme.**  
Registers the Movie custom post type, taxonomies, meta boxes, and widgets.

---

## Requirements

- WordPress 5.0+
- PHP 7.2+
- [HOA Movie Mart theme](https://github.com/helpofai/HOA-Movie-Mart) (recommended)

---

## Features

- **Movie Custom Post Type** — full movie entries with featured images, excerpts, and comments
- **Taxonomies** — Genre, Year, Quality, Director, Cast
- **Meta Boxes** — IMDb rating, runtime, trailer URL, language, download links (JSON), DMCA toggle
- **Report CPT** — dead link reports from users
- **Request CPT** — user movie/series requests
- **Custom Widgets** — Latest Movies, Visual Recent Posts, Genre/Taxonomy list
- **Download Link Repeater** — multiple sources per movie with encryption support
- **GitHub Auto-Updates** — pulls latest release from GitHub, no manual zip uploads

---

## Installation

1. Download the [latest release](https://github.com/helpofai/hoa-movie-mart-core/releases/latest) zip
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Choose the zip file and click **Install Now**
4. **Activate** the plugin

Or clone into `wp-content/plugins/`:

```bash
cd wp-content/plugins
git clone https://github.com/helpofai/hoa-movie-mart-core.git
```

---

## GitHub Auto-Updates

This plugin checks the GitHub Releases API for new versions and integrates with WordPress's native update system.

- **Public repos** — works out of the box (60 API requests/hour)
- **Private repos** — add a GitHub personal access token in the HOA Movie Mart theme settings (5,000 req/h)
- Updates appear under **Dashboard → Updates** and can be installed with one click

---

## Development

```bash
git clone https://github.com/helpofai/hoa-movie-mart-core.git
cd hoa-movie-mart-core
```

### Releasing a new version

1. Update the `Version:` header in `hoa-movie-mart-core.php`
2. Update `CHANGELOG.md`
3. Commit and push
4. Create a [GitHub Release](https://github.com/helpofai/hoa-movie-mart-core/releases/new) with a tag like `v1.5.0`
5. WordPress sites will detect the update within 12 hours

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md)

---

## License

GPL-2.0-or-later — same as WordPress.

---

## Author

Built by the [HelpOfAi](https://helpofai.com) team.
