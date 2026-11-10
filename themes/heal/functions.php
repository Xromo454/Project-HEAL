<?php
/**
 * HEAL theme setup.
 * Project data and logic live in the "HEAL Data" plugin; this theme only renders it.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);

    // The block editor uses the same fonts, colours and content styles as the site.
    add_theme_support('editor-styles');
    add_editor_style('assets/editor.css');
});

add_action('wp_enqueue_scripts', function () {
    $ver = wp_get_theme()->get('Version');
    wp_enqueue_style('heal', get_stylesheet_uri(), [], $ver);
    wp_enqueue_script('heal-nav', get_theme_file_uri('assets/nav.js'), [], $ver, ['strategy' => 'defer', 'in_footer' => true]);
    // Lightbox on any page that has a Gallery block.
    if (is_singular() && has_block('core/gallery')) {
        wp_enqueue_script('heal-gallery', get_theme_file_uri('assets/gallery.js'), [], $ver, ['strategy' => 'defer', 'in_footer' => true]);
    }
});

/** Main navigation. Same items as NAV in the Astro site.ts. Links point to page slugs. */
function heal_nav_items(): array
{
    return [
        ['Home', home_url('/')],
        ['Project', home_url('/project/')],
        ['Work plan', home_url('/work-plan/')],
        ['Consortium & team', home_url('/consortium/')],
        ['Results', home_url('/results/')],
        ['News & events', home_url('/news/')],
        ['Gallery', home_url('/gallery/')],
        ['Contact', home_url('/contact/')],
    ];
}

function heal_footer_info_links(): array
{
    return [
        ['Privacy policy', home_url('/privacy/')],
        ['Accessibility statement', home_url('/accessibility/')],
        ['Legal notice', home_url('/legal/')],
        ['Media kit', home_url('/media-kit/')],
    ];
}

/**
 * Languages for the header switch (EN / EL), same as the Astro site.
 * If Polylang is active, its real language URLs are used; otherwise Greek
 * links to the home page and is marked "coming soon".
 */
function heal_languages(): array
{
    $langs = [
        'en' => ['name' => 'English',  'title' => 'English'],
        'el' => ['name' => 'Ελληνικά', 'title' => 'Greek version coming soon'],
    ];

    $pll = function_exists('pll_the_languages') ? pll_the_languages(['raw' => 1, 'hide_if_empty' => 0]) : [];
    $current = function_exists('pll_current_language') ? pll_current_language() : 'en';

    $out = [];
    foreach ($langs as $code => $l) {
        $url = $pll[$code]['url'] ?? home_url('/');
        $title = isset($pll[$code]) ? $l['name'] : $l['title'];
        $out[] = ['code' => $code, 'name' => $l['name'], 'title' => $title, 'url' => $url, 'current' => $code === $current];
    }
    return $out;
}

/** True if $url is the page being viewed (for aria-current). */
function heal_is_current(string $url): bool
{
    $current = home_url(add_query_arg([]));
    return untrailingslashit($url) === untrailingslashit(strtok($current, '?'));
}

/** Small "↗" arrow used after text links in the design. */
function heal_arrow(): string
{
    return '<span class="arrow" aria-hidden="true">↗</span>';
}

/** Safe wrapper: the theme still renders if the HEAL Data plugin is off. */
function heal_has_data(): bool
{
    return function_exists('heal_public_deliverables');
}
