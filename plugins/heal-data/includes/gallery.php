<?php
/**
 * Gallery.
 *
 * The photos live in the WordPress Media Library, so the PI can add and replace them,
 * write alt text and captions, and see them in the editor.
 *
 * data/gallery/*.json + data/gallery/images/ are only the STARTING set. They are imported
 * into the Media Library once (a few photos per request, so it never times out), and the
 * Gallery page is then rebuilt from normal Gallery blocks. After that the files are not used.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Bump to import newly added starting photos and rebuild the page. */
const HEAL_GALLERY_IMPORT_VERSION = '1';
const HEAL_GALLERY_SRC_META = '_heal_gallery_src';

const HEAL_GALLERY_SECTIONS = [
    'moodboard' => ['Moodboard', 'The visual direction of HEAL.', 'Colours, textures and references that shaped the look of the project.'],
    'stock'     => ['Stock photos', 'Photos used on this website.', 'Stock images used across the site, with credit to their photographers.'],
    'limassol'  => ['Limassol', 'The Limassol living lab.', 'Streets, routes and places in Limassol, where the HEAL pilot takes place.'],
    'locations' => ['Other locations', 'Partner sites and branches.', 'Photos from the other sites of the organisations involved.'],
];

/** The starting photos from data/gallery/*.json, sorted by order, then name. Invalid ones are skipped. */
function heal_gallery_photos(): array
{
    $photos = [];
    $dir = HEAL_DATA_DIR . 'gallery/';
    foreach (glob($dir . '*.json') ?: [] as $jsonFile) {
        $p = json_decode((string) file_get_contents($jsonFile), true);
        if (!is_array($p) || empty($p['image']) || empty($p['alt']) || !isset(HEAL_GALLERY_SECTIONS[$p['section'] ?? ''])) {
            continue;
        }
        if ($p['section'] === 'stock' && empty($p['credit'])) {
            continue; // stock photos need a credit
        }
        $file = $dir . ltrim(preg_replace('#^\./#', '', $p['image']), '/');
        if (!is_readable($file)) {
            continue;
        }
        $p['id']    = basename($jsonFile, '.json');
        $p['file']  = $file;
        $p['order'] = (int) ($p['order'] ?? 0);
        $photos[] = $p;
    }
    usort($photos, fn($a, $b) => $a['order'] <=> $b['order'] ?: strcmp($a['id'], $b['id']));
    return $photos;
}

/** Attachment id of an imported starting photo, or 0. */
function heal_gallery_attachment_id(string $srcId): int
{
    $ids = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => HEAL_GALLERY_SRC_META,
        'meta_value'     => $srcId,
    ]);
    return (int) ($ids[0] ?? 0);
}

/** Caption shown under a photo: optional caption, then "Photo: credit" (linked if there is a URL). */
function heal_gallery_caption(array $p): string
{
    $parts = [];
    if (!empty($p['caption'])) {
        $parts[] = esc_html($p['caption']);
    }
    if (!empty($p['credit'])) {
        $who = !empty($p['creditUrl'])
            ? '<a href="' . esc_url($p['creditUrl']) . '">' . esc_html($p['credit']) . '</a>'
            : esc_html($p['credit']);
        $parts[] = 'Photo: ' . $who;
    }
    return implode(' · ', $parts);
}

/**
 * Imports starting photos that are not in the Media Library yet.
 * Stops after $budget seconds. Returns true when everything is imported.
 */
function heal_gallery_import(float $budget = 8.0): bool
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $start = microtime(true);
    foreach (heal_gallery_photos() as $p) {
        if (heal_gallery_attachment_id($p['id'])) {
            continue;
        }
        if (microtime(true) - $start > $budget) {
            return false; // continue on the next request
        }

        $tmp = wp_tempnam($p['file']);
        if (!$tmp || !copy($p['file'], $tmp)) {
            error_log('HEAL gallery: cannot copy ' . $p['file']);
            continue;
        }
        $id = media_handle_sideload(
            ['name' => basename($p['file']), 'tmp_name' => $tmp],
            0,
            null,
            [
                'post_title'   => wp_trim_words($p['alt'], 8, ''),
                'post_excerpt' => heal_gallery_caption($p),
            ]
        );
        if (is_wp_error($id)) {
            error_log('HEAL gallery: import failed for ' . $p['id'] . ': ' . $id->get_error_message());
            @unlink($tmp);
            continue;
        }
        update_post_meta($id, '_wp_attachment_image_alt', $p['alt']);
        update_post_meta($id, HEAL_GALLERY_SRC_META, $p['id']);
    }
    return true;
}

/** One core/gallery block (images link to the full file; the theme opens them in a lightbox). */
function heal_gallery_block(array $items): string
{
    $inner = '';
    foreach ($items as $p) {
        $id    = (int) $p['attachment'];
        $large = wp_get_attachment_image_src($id, 'large');
        $full  = wp_get_attachment_url($id);
        if (!$large || !$full) {
            continue;
        }
        $alt     = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
        $caption = heal_gallery_caption($p);
        $attrs   = wp_json_encode(['id' => $id, 'sizeSlug' => 'large', 'linkDestination' => 'media']);

        $inner .= "<!-- wp:image $attrs -->\n"
            . '<figure class="wp-block-image size-large"><a href="' . esc_url($full) . '">'
            . '<img src="' . esc_url($large[0]) . '" alt="' . esc_attr($alt) . '" class="wp-image-' . $id . '"/></a>'
            . ($caption !== '' ? '<figcaption class="wp-element-caption">' . $caption . '</figcaption>' : '')
            . "</figure>\n<!-- /wp:image -->";
    }
    if ($inner === '') {
        return '';
    }
    $attrs = wp_json_encode(['columns' => 3, 'imageCrop' => false, 'linkTo' => 'media']);
    return "<!-- wp:gallery $attrs -->\n"
        . '<figure class="wp-block-gallery has-nested-images columns-3">' . $inner . "</figure>\n<!-- /wp:gallery -->";
}

/**
 * The block markup that follows the intro on the Gallery page: section links,
 * then for each section a heading, a sentence and a Gallery block.
 * Returns '' until the starting photos are imported.
 */
function heal_gallery_blocks(): string
{
    if (get_option('heal_gallery_imported') !== HEAL_GALLERY_IMPORT_VERSION) {
        return '';
    }

    $bySection = [];
    foreach (heal_gallery_photos() as $p) {
        $p['attachment'] = heal_gallery_attachment_id($p['id']);
        if ($p['attachment']) {
            $bySection[$p['section']][] = $p;
        }
    }

    $links = [];
    foreach (HEAL_GALLERY_SECTIONS as $key => [$label]) {
        $links[] = '<a href="#' . $key . '">' . esc_html($label) . '</a>';
    }
    $blocks = ["<!-- wp:paragraph {\"className\":\"page-nav\"} -->\n<p class=\"page-nav\">" . implode(' ', $links) . "</p>\n<!-- /wp:paragraph -->"];

    $n = 0;
    foreach (HEAL_GALLERY_SECTIONS as $key => [$label, $title, $text]) {
        $n++;
        $blocks[] = "<!-- wp:paragraph {\"className\":\"eyebrow\"} -->\n<p class=\"eyebrow\">" . sprintf('%02d', $n) . ' / ' . esc_html($label) . "</p>\n<!-- /wp:paragraph -->";
        $blocks[] = '<!-- wp:heading ' . wp_json_encode(['anchor' => $key]) . " -->\n<h2 class=\"wp-block-heading\" id=\"$key\">" . esc_html($title) . "</h2>\n<!-- /wp:heading -->";
        $blocks[] = "<!-- wp:paragraph -->\n<p>" . esc_html($text) . "</p>\n<!-- /wp:paragraph -->";

        $gallery = heal_gallery_block($bySection[$key] ?? []);
        $blocks[] = $gallery !== ''
            ? $gallery
            : "<!-- wp:paragraph {\"className\":\"small\"} -->\n<p class=\"small\">Photos coming soon. Add a Gallery block here to show photos in this section.</p>\n<!-- /wp:paragraph -->";
    }
    return implode("\n\n", $blocks);
}

add_action('init', function () {
    if (get_option('heal_gallery_imported') === HEAL_GALLERY_IMPORT_VERSION) {
        return;
    }
    // One import at a time (two requests at once would import a photo twice).
    if (get_transient('heal_gallery_lock')) {
        return;
    }
    set_transient('heal_gallery_lock', 1, 60);

    if (heal_gallery_import()) {
        update_option('heal_gallery_imported', HEAL_GALLERY_IMPORT_VERSION);
        if (function_exists('heal_seed_pages')) {
            heal_seed_pages(['gallery']); // rebuild the Gallery page (only if nobody edited it)
        }
    }
    delete_transient('heal_gallery_lock');
}, 30);
