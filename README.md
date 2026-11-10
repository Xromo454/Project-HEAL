# HEAL — WordPress site

WordPress version of the HEAL project website (COM-B & Exposome Partnership for Health Engagement).
It is a port of the Astro site from the `develop` branch of this repository.

This branch has its own history and is never merged into `main` or `develop`.
It holds only our own code; WordPress core and third-party plugins are not stored here.

## Contents

```
themes/heal/          HEAL theme: layout, styles, navigation (renders data, holds no project data)
plugins/heal-data/    HEAL Data plugin: project data, shortcodes, page content
```

### HEAL theme (`themes/heal`)

| File | Purpose |
|---|---|
| `style.css` | Theme header and all styles; fonts are self-hosted (no Google requests, GDPR) |
| `functions.php` | Theme setup, main and footer navigation, helpers |
| `header.php`, `footer.php` | Site header and footer |
| `front-page.php` | Home page |
| `page.php`, `index.php` | Regular pages and fallback |
| `assets/` | Fonts, logo, `nav.js` (mobile menu) |

The theme still renders if the HEAL Data plugin is off, but without project data.

### HEAL Data plugin (`plugins/heal-data`)

| Path | Purpose |
|---|---|
| `data/*.json` | Deliverables, work packages, milestones, partners |
| `content/pages/*.html` | Content of the site pages |
| `includes/config.php` | Project facts (name, project number, contact email, public deliverables) |
| `includes/status.php` | Deliverable status (Planned / In progress / Published) from the project month |
| `includes/settings.php` | "HEAL project start date" field in **Settings → General** |
| `includes/shortcodes.php` | Shortcodes, see below |
| `includes/pages.php` | Creates the site pages on first run |

Shortcodes:

| Shortcode | Output |
|---|---|
| `[heal_results]` | Table of public deliverables with status |
| `[heal_work_plan]` | Work packages and milestones |
| `[heal_consortium]` | Partners |
| `[heal_project_number]` | Grant project number |
| `[heal_contact_email]` | Contact email link |

## Requirements

- WordPress 7.1+
- PHP 8.0+

The local site also has Elementor, TablePress and TranslatePress installed.
The HEAL theme and plugin do not depend on them.

## Local setup

1. Create a WordPress site (for example with [Local](https://localwp.com/)).
2. Copy (or link, see below) the folders into the site's `wp-content`:
   - `themes/heal` → `wp-content/themes/heal`
   - `plugins/heal-data` → `wp-content/plugins/heal-data`
3. In the admin:
   - **Plugins** → activate **HEAL Data**.
   - **Appearance → Themes** → activate **HEAL**.
   - **Settings → Permalinks** → choose **Post name** (pages use `/project/`, `/work-plan/` and so on).
   - **Settings → General** → set **HEAL project start date**. Empty means every deliverable shows "Planned".
4. Open the site once: the plugin creates the pages from `content/pages/`.

### Working without copying files

Instead of copying, link the folders from the repository into `wp-content`
(Windows, `cmd`, after removing the copies from `wp-content`):

```
mklink /J "<site>\wp-content\themes\heal" "<repo>\themes\heal"
mklink /J "<site>\wp-content\plugins\heal-data" "<repo>\plugins\heal-data"
```

The links work only while this branch is checked out. To keep the Astro branches available at the same time,
check this branch out in a separate folder with `git worktree add ../Project-HEAL-wp feat/wordpress`
and link to that folder.

## Changing content

- **Project data** (deliverables, work packages, milestones, partners): edit `plugins/heal-data/data/*.json`.
- **Project facts** (project number, contact email, public deliverables): edit `plugins/heal-data/includes/config.php`.
- **Page text**: pages are created from `content/pages/*.html` only once and are never overwritten.
  After the first run, edit pages in the WordPress admin.
- **New pages**: add the HTML file and an entry in `heal_page_list()` in `includes/pages.php`,
  then increase `HEAL_PAGES_VERSION`. Only missing pages are created.
- In page HTML, `{home}` is replaced with the site URL.

## What is not in Git

- WordPress core and `wp-config.php` (database passwords)
- Third-party themes and plugins
- `wp-content/uploads/` (media)
- The database: pages edited in the admin, menus and settings. Move them with the export in Local
  or a migration plugin.

The `.gitignore` allows only `themes/heal/` and `plugins/heal-data/`, so other files cannot be committed by mistake.
