<?php
/**
 * Creates the site pages from content/pages/*.html.
 *
 * - Runs once per HEAL_PAGES_VERSION (bump it to create pages added later).
 * - Only creates pages that do not exist yet: it never overwrites a page someone edited.
 * - "{home}" in the HTML becomes the site URL, so links work on a sub-site too.
 * - Content goes into one "Custom HTML" block, so WordPress does not reformat it.
 */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_PAGES_VERSION = 1;

/** slug => [title, parent slug or null, menu order] */
function heal_page_list(): array
{
    return [
        'project'       => ['Overview & aims', null, 1],
        'how-it-works'  => ['How it works', 'project', 2],
        'living-lab'    => ['Limassol living lab', 'project', 3],
        'work-plan'     => ['Work plan', null, 4],
        'consortium'    => ['Consortium & team', null, 5],
        'results'       => ['Results', null, 6],
        'news'          => ['News & events', null, 7],
        'participate'   => ['Participate', null, 8],
        'contact'       => ['Contact', null, 9],
        'privacy'       => ['Privacy policy', null, 20],
        'accessibility' => ['Accessibility statement', null, 21],
        'legal'         => ['Legal notice', null, 22],
        'media-kit'     => ['Media kit', null, 23],
    ];
}

function heal_page_content(string $slug): string
{
    $file = plugin_dir_path(__DIR__) . 'content/pages/' . $slug . '.html';
    $html = is_readable($file) ? (string) file_get_contents($file) : '';
    if ($slug === 'results' && $html === '') {
        $html = "<p class=\"lead\">Public deliverables of the HEAL project. Other deliverables are confidential under the grant agreement.</p>\n\n[heal_results]";
    }
    $html = str_replace('{home}', untrailingslashit(home_url()), $html);
    return "<!-- wp:html -->\n" . trim($html) . "\n<!-- /wp:html -->";
}

/** Creates missing pages. Returns the slugs it created. */
function heal_seed_pages(): array
{
    $created = [];
    $ids = [];

    foreach (heal_page_list() as $slug => [$title, $parent, $order]) {
        $parentId = $parent ? ($ids[$parent] ?? 0) : 0;
        $path = $parent ? "$parent/$slug" : $slug;
        $existing = get_page_by_path($path, OBJECT, 'page');

        if ($existing) {
            $ids[$slug] = $existing->ID;
            continue;
        }

        $id = wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_parent'  => $parentId,
            'menu_order'   => $order,
            'post_content' => heal_page_content($slug),
        ], true);

        if (!is_wp_error($id)) {
            $ids[$slug] = $id;
            $created[] = $path;
        }
    }
    return $created;
}

add_action('init', function () {
    if ((int) get_option('heal_pages_version', 0) >= HEAL_PAGES_VERSION) {
        return;
    }
    // Set the flag first, so two requests at once cannot create duplicates.
    update_option('heal_pages_version', HEAL_PAGES_VERSION);
    heal_seed_pages();
}, 20);
