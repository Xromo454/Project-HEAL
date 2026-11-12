<?php
/**
 * Creates and refreshes the site pages from content/pages/*.html.
 *
 * - The files hold WordPress block markup, so the PI edits normal blocks in the editor.
 * - Runs once per HEAL_PAGES_VERSION. Bump it after changing a content file.
 * - Missing pages are created.
 * - An existing page is refreshed from its file ONLY if nobody edited it in the admin.
 *   We store a hash of what we wrote (_heal_seed_hash); if the content no longer
 *   matches the hash, a person changed it and we leave it alone.
 * - "{home}" in the files becomes the site URL, so links also work on a sub-site.
 */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_PAGES_VERSION = 12; // 4: gallery page, 5: gallery from Media Library blocks, 6: news list, 7: "Partners" heading on Consortium, 8: timeline and work package pages, 9-11: logo downloads on the Media kit, 12: contact form
const HEAL_SEED_HASH_META = '_heal_seed_hash';

/** slug => [title, parent slug or null, menu order] */
function heal_page_list(): array
{
    $pages = [
        'project'       => ['Overview & aims', null, 1],
        'how-it-works'  => ['How it works', 'project', 2],
        'living-lab'    => ['Limassol living lab', 'project', 3],
        'work-plan'     => ['Work plan', null, 4],
        'consortium'    => ['Consortium & team', null, 5],
        'results'       => ['Results', null, 6],
        'news'          => ['News & events', null, 7],
        'gallery'       => ['Gallery', null, 7],
        'participate'   => ['Participate', null, 8],
        'contact'       => ['Contact', null, 9],
        'privacy'       => ['Privacy policy', null, 20],
        'accessibility' => ['Accessibility statement', null, 21],
        'legal'         => ['Legal notice', null, 22],
        'media-kit'     => ['Media kit', null, 23],
    ];

    // One page per work package under Work plan: /work-plan/wp1/ ... (title from data/workPackages.json).
    foreach (heal_work_packages() as $i => $wp) {
        $pages[strtolower($wp['id'])] = [$wp['id'] . ': ' . $wp['title'], 'work-plan', $i + 1];
    }
    return $pages;
}

function heal_page_content(string $slug): string
{
    $file = plugin_dir_path(__DIR__) . 'content/pages/' . $slug . '.html';
    $html = is_readable($file) ? (string) file_get_contents($file) : '';
    if ($slug === 'gallery' && function_exists('heal_gallery_blocks')) {
        $html = trim($html) . "\n\n" . heal_gallery_blocks(); // photos come from the Media Library
    }
    return trim(str_replace('{home}', untrailingslashit(home_url()), $html));
}

/**
 * Pages made by version 1 of this plugin had no hash and were a single Custom HTML block.
 * Treat exactly that shape as "not edited".
 */
function heal_is_legacy_seed(string $content): bool
{
    return preg_match_all('/<!--\s*wp:html\s*-->/', $content) === 1
        && preg_match('/<!--\s*wp:(?!html\b)/', $content) === 0;
}

/**
 * Writes content without WordPress' kses filter: the text comes from our own plugin files.
 * (On a visitor's request kses is active and would strip tags like <details>.)
 */
function heal_write_page(array $args): int|WP_Error
{
    kses_remove_filters();
    $args['post_content'] = wp_slash($args['post_content']);
    $id = !empty($args['ID']) ? wp_update_post($args, true) : wp_insert_post($args, true);
    kses_init();

    if (!is_wp_error($id) && $id) {
        // Hash what is really stored, not what we sent.
        update_post_meta($id, HEAL_SEED_HASH_META, md5((string) get_post_field('post_content', $id, 'raw')));
    }
    return $id;
}

/**
 * Creates missing pages, refreshes untouched ones. Returns what it did.
 * With $only (a list of slugs) it handles just those pages.
 */
function heal_seed_pages(array $only = []): array
{
    $done = ['created' => [], 'updated' => [], 'kept' => []];
    $ids = [];

    foreach (heal_page_list() as $slug => [$title, $parent, $order]) {
        $parentId = $parent ? ($ids[$parent] ?? 0) : 0;
        $path = $parent ? "$parent/$slug" : $slug;
        $existing = get_page_by_path($path, OBJECT, 'page');

        if ($only && !in_array($slug, $only, true)) {
            if ($existing) {
                $ids[$slug] = $existing->ID;
            }
            continue;
        }
        $content = heal_page_content($slug);

        if (!$existing) {
            $id = heal_write_page([
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_parent'  => $parentId,
                'menu_order'   => $order,
                'post_content' => $content,
            ]);
            if (!is_wp_error($id)) {
                $ids[$slug] = $id;
                $done['created'][] = $path;
            }
            continue;
        }

        $ids[$slug] = $existing->ID;
        $storedHash = (string) get_post_meta($existing->ID, HEAL_SEED_HASH_META, true);
        $edited = $storedHash !== ''
            ? $storedHash !== md5($existing->post_content)
            : !heal_is_legacy_seed($existing->post_content);

        if ($edited) {
            $done['kept'][] = $path;
            continue;
        }

        heal_write_page(['ID' => $existing->ID, 'post_content' => $content]);
        $done['updated'][] = $path;
    }
    return $done;
}

add_action('init', function () {
    if ((int) get_option('heal_pages_version', 0) >= HEAL_PAGES_VERSION) {
        return;
    }
    // Set the flag first, so two requests at once cannot run the seeding twice.
    update_option('heal_pages_version', HEAL_PAGES_VERSION);
    update_option('heal_pages_last_run', heal_seed_pages(), false);
}, 20);
