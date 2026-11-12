<?php
/**
 * SEO: page titles, meta description, Open Graph (link previews) and structured data
 * (schema.org ResearchProject on the home page).
 *
 * - Descriptions: the page's Excerpt (the PI can fill it in the editor) → text below → first normal
 *   paragraph of the page → site description.
 * - Link preview image: put assets/og-default.png (1200 x 630) in the theme, or use the
 *   "heal_og_image" filter. Without it the preview has no image.
 * - Skipped when a SEO plugin is active, so tags are not doubled.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Curated descriptions by page slug (about 120–160 characters). Wording follows the content rules: aims, not results. */
const HEAL_SEO_DESCRIPTIONS = [
    'home'          => 'HEAL aims to explore how behavioural science and environmental context can support health engagement through everyday cycling in Limassol.',
    'project'       => 'The aims of HEAL: combining the COM-B behavioural model with the exposome to support everyday cycling and the prevention of noncommunicable diseases.',
    'how-it-works'  => 'How HEAL works: environmental data sets the moment, a just-in-time nudge and a micro-incentive support a ride, and verified rides feed back as data.',
    'living-lab'    => 'Limassol is one of 100 EU Mission cities aiming for climate neutrality by 2030. HEAL aims to make it a living lab for cycling and health.',
    'work-plan'     => 'The HEAL work plan: five work packages over 24 months, with a six-month intervention in a pilot study with 20 adults.',
    'consortium'    => 'The people behind HEAL: Cyprus University of Technology, NextBike Cyprus and Limassol Municipality, with the research team.',
    'results'       => 'Public deliverables of the HEAL project and their planned release: Data Management Plan, Implementation Toolkit, manuscripts, final report and database.',
    'news'          => 'Project news, events and planned milestones of the HEAL research project in Limassol.',
    'gallery'       => 'Photos from Limassol and the visual identity of the HEAL research project.',
    'participate'   => 'Information about taking part in the HEAL pilot study in Limassol. Recruitment is not open yet.',
    'contact'       => 'Contact the HEAL project team at the Cyprus University of Technology.',
    'privacy'       => 'Privacy policy of the HEAL website: no tracking cookies, and how we handle messages sent to us.',
    'accessibility' => 'Accessibility statement of the HEAL website, which aims to meet WCAG 2.1 level AA.',
    'legal'         => 'Legal notice, funding statement and copyright of the HEAL project website.',
    'media-kit'     => 'Logos, a short description and approved images of the HEAL project for journalists and partners.',
];

function heal_seo_plugin_active(): bool
{
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('SEOPRESS_VERSION') || defined('AIOSEO_VERSION');
}

/** The PI can write a custom description in the page's "Excerpt" box. */
add_action('init', function () {
    add_post_type_support('page', 'excerpt');
});

/* ---------- Page title: "Page | HEAL"; home: "HEAL | tagline" ---------- */

add_filter('document_title_separator', fn() => '|');

add_filter('document_title_parts', function (array $parts): array {
    if (heal_seo_plugin_active()) {
        return $parts;
    }
    if (is_front_page()) {
        return ['title' => HEAL_SITE['name'], 'tagline' => HEAL_SITE['fullName']];
    }
    unset($parts['tagline']);
    $parts['site'] = HEAL_SITE['name'];
    return $parts;
});

/* ---------- Description ---------- */

function heal_seo_description(): string
{
    if (is_front_page()) {
        return HEAL_SEO_DESCRIPTIONS['home'];
    }
    if (!is_singular()) {
        return HEAL_SITE['description'];
    }
    $post = get_queried_object();

    $excerpt = trim(wp_strip_all_tags((string) $post->post_excerpt));
    if ($excerpt !== '') {
        return $excerpt;
    }
    // Work package pages (/work-plan/wp1/ ...): the objective of the work package.
    if ($post->post_type === 'page' && preg_match('/^wp[1-5]$/', $post->post_name) && function_exists('heal_find_wp')) {
        $wp = heal_find_wp(strtoupper($post->post_name));
        if ($wp) {
            return wp_trim_words($wp['objective'], 28, '…');
        }
    }
    if ($post->post_type === 'page' && isset(HEAL_SEO_DESCRIPTIONS[$post->post_name])) {
        return HEAL_SEO_DESCRIPTIONS[$post->post_name];
    }
    // First normal paragraph (skips draft notes, notices, small print and link rows).
    foreach (parse_blocks($post->post_content) as $block) {
        if ($block['blockName'] !== 'core/paragraph') {
            continue;
        }
        if (preg_match('/\b(draft-note|notice|small|eyebrow|page-nav)\b/', (string) ($block['attrs']['className'] ?? ''))) {
            continue;
        }
        $text = trim(wp_strip_all_tags($block['innerHTML']));
        if ($text !== '') {
            return wp_trim_words($text, 28, '…');
        }
    }
    return HEAL_SITE['description'];
}

/* ---------- Structured data (home page) ---------- */

function heal_seo_jsonld(): array
{
    $partners = [];
    foreach (heal_partners() as $p) {
        if ($p['role'] === 'End user / stakeholder') {
            continue; // the formal consortium is the host and partner 1
        }
        $org = ['@type' => 'Organization', 'name' => $p['name']];
        if (!empty($p['website'])) {
            $org['url'] = $p['website'];
        }
        $partners[] = $org;
    }

    $project = [
        '@type'         => 'ResearchProject',
        '@id'           => home_url('/#project'),
        'name'          => 'HEAL mission: a COM-B and Exposome Partnership for Health Engagement',
        'alternateName' => HEAL_SITE['name'],
        'description'   => HEAL_SEO_DESCRIPTIONS['home'],
        'url'           => home_url('/'),
        'identifier'    => HEAL_SITE['projectNumber'],
        'funder'        => ['@type' => 'Organization', 'name' => 'Research and Innovation Foundation (Cyprus)'],
        'member'        => $partners,
    ];

    // Dates only once the PI has set the project start date (Settings → General).
    $start = function_exists('heal_start_date') ? heal_start_date() : null;
    if ($start && ($d = heal_parse_date($start))) {
        $project['startDate'] = $d->format('Y-m-d');
        $project['endDate']   = $d->modify('+24 months')->modify('-1 day')->format('Y-m-d');
    }

    return [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type'       => 'WebSite',
                '@id'         => home_url('/#website'),
                'url'         => home_url('/'),
                'name'        => HEAL_SITE['name'],
                'description' => HEAL_SITE['fullName'],
                'inLanguage'  => 'en',
            ],
            $project,
        ],
    ];
}

/* ---------- Tags in <head> ---------- */

add_action('wp_head', function () {
    if (heal_seo_plugin_active() || is_404()) {
        return;
    }

    $desc = heal_seo_description();
    $url  = is_singular() ? get_permalink(get_queried_object_id()) : home_url('/');
    if (is_front_page()) {
        $title = HEAL_SITE['name'] . ' – ' . HEAL_SITE['fullName'];
    } elseif (is_singular()) {
        $title = get_the_title(get_queried_object_id());
    } else {
        $title = HEAL_SITE['name'];
    }

    // Link preview image: the post's featured image, else the theme's default image.
    $image = '';
    if (is_singular() && has_post_thumbnail(get_queried_object_id())) {
        $image = (string) get_the_post_thumbnail_url(get_queried_object_id(), 'large');
    } elseif (file_exists(get_theme_file_path('assets/og-default.png'))) {
        $image = get_theme_file_uri('assets/og-default.png');
    }
    $image = apply_filters('heal_og_image', $image);

    $tags = [
        ['name', 'description', $desc],
        ['property', 'og:site_name', HEAL_SITE['name']],
        ['property', 'og:type', is_singular('post') ? 'article' : 'website'],
        ['property', 'og:title', $title],
        ['property', 'og:description', $desc],
        ['property', 'og:url', $url],
        ['property', 'og:locale', get_locale()],
        ['name', 'twitter:card', $image ? 'summary_large_image' : 'summary'],
    ];
    if ($image) {
        $tags[] = ['property', 'og:image', $image];
        $tags[] = ['property', 'og:image:width', '1200'];
        $tags[] = ['property', 'og:image:height', '630'];
    }

    foreach ($tags as [$attr, $key, $value]) {
        printf("<meta %s=\"%s\" content=\"%s\">\n", $attr, esc_attr($key), esc_attr($value));
    }

    if (is_front_page()) {
        echo '<script type="application/ld+json">'
            . wp_json_encode(heal_seo_jsonld(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
            . "</script>\n";
    }
}, 5);
