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
});

add_action('wp_enqueue_scripts', function () {
    $ver = wp_get_theme()->get('Version');
    wp_enqueue_style('heal', get_stylesheet_uri(), [], $ver);
    wp_enqueue_script('heal-nav', get_theme_file_uri('assets/nav.js'), [], $ver, ['strategy' => 'defer', 'in_footer' => true]);
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
