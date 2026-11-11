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
    add_theme_support('post-thumbnails'); // featured image on news posts (also used for link previews)
    add_theme_support('automatic-feed-links'); // RSS link in the page head

    // The block editor uses the same fonts, colours and content styles as the site.
    add_theme_support('editor-styles');
    add_editor_style('assets/editor.css');
});

add_action('wp_enqueue_scripts', function () {
    $ver = wp_get_theme()->get('Version');
    wp_enqueue_style('heal', get_stylesheet_uri(), [], $ver);
    wp_enqueue_script('heal-nav', get_theme_file_uri('assets/nav.js'), [], $ver, ['strategy' => 'defer', 'in_footer' => true]);
    // Lightbox on any page with a Gallery block, an Image block or a classic [gallery].
    if (is_singular() && (has_block('core/gallery') || has_block('core/image') || has_shortcode((string) get_post_field('post_content'), 'gallery'))) {
        wp_enqueue_script('heal-gallery', get_theme_file_uri('assets/gallery.js'), [], $ver, ['strategy' => 'defer', 'in_footer' => true]);
    }
});

/** Classic [gallery] shortcode: use the large photo and link to the file unless the editor chose otherwise. */
add_filter('shortcode_atts_gallery', function (array $out, array $pairs, $atts): array {
    $atts = (array) $atts;
    if (empty($atts['size'])) {
        $out['size'] = 'large';
    }
    if (empty($atts['link'])) {
        $out['link'] = 'file';
    }
    return $out;
}, 10, 3);

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

/* ---------- HEAL icon instead of the WordPress logo ---------- */

/**
 * Browser tab / home screen icon (the HEAL bike). A Site Icon set in
 * Appearance → Customize → Site Identity still wins.
 */
function heal_icon_tags(): void
{
    if (has_site_icon()) {
        return;
    }
    $icon = fn(string $file): string => esc_url(get_theme_file_uri('assets/icons/' . $file));
    echo '<link rel="icon" href="' . $icon('favicon.ico') . '" sizes="48x48">' . "\n";
    echo '<link rel="icon" type="image/png" href="' . $icon('icon-192.png') . '" sizes="192x192">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . $icon('apple-touch-icon.png') . '">' . "\n";
}
add_action('wp_head', 'heal_icon_tags', 2);
add_action('admin_head', 'heal_icon_tags');
add_action('login_head', 'heal_icon_tags');

// /favicon.ico would otherwise redirect to the WordPress "W" logo.
add_action('do_faviconico', function () {
    if (!has_site_icon()) {
        wp_redirect(get_theme_file_uri('assets/icons/favicon.ico'), 301);
        exit;
    }
});

// Login page (/wp-login.php): HEAL icon linking to the site instead of the WordPress logo.
add_action('login_enqueue_scripts', function () {
    $logo = esc_url(get_theme_file_uri('assets/icons/icon-192.png'));
    echo '<style>
        body.login { background: #fff7ed; }
        #login h1 a {
            background-image: url("' . $logo . '");
            background-size: contain;
            width: 96px;
            height: 96px;
        }
    </style>';
});
add_filter('login_headerurl', fn() => home_url('/'));
add_filter('login_headertext', fn() => get_bloginfo('name'));
