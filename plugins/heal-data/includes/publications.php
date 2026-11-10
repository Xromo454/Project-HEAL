<?php
/**
 * Publications (manuscripts, conference papers). Shown under "Publications" on the Results page.
 *
 * The PI adds them in the admin menu "Publications": title plus a small form with authors,
 * journal, year, DOI, open access and the deliverable it belongs to (e.g. D11).
 * They have no pages of their own; the list is built by [heal_publications]
 * (and is included automatically in [heal_results]).
 */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_DOI_PATTERN = '/^10\.\d{4,9}\/\S+$/';

add_action('init', function () {
    register_post_type('heal_publication', [
        'labels' => [
            'name'          => 'Publications',
            'singular_name' => 'Publication',
            'add_new'       => 'Add publication',
            'add_new_item'  => 'Add publication',
            'edit_item'     => 'Edit publication',
            'all_items'     => 'All publications',
            'not_found'     => 'No publications yet.',
        ],
        'public'       => false,   // no page of its own and no URL
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-book-alt',
        'menu_position' => 21,
        'supports'     => ['title'],
        'rewrite'      => false,
        'has_archive'  => false,
    ]);
});

add_action('add_meta_boxes', function () {
    add_meta_box('heal_publication_details', 'Publication details', 'heal_publication_box', 'heal_publication', 'normal', 'high');
});

function heal_publication_box(WP_Post $post): void
{
    wp_nonce_field('heal_publication_save', 'heal_publication_nonce');
    $v = fn(string $key): string => (string) get_post_meta($post->ID, '_heal_pub_' . $key, true);
    ?>
    <p class="description">The title above is the title of the paper. Fill in what you know; empty fields are skipped.</p>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="heal_pub_authors">Authors</label></th>
            <td><input type="text" class="large-text" id="heal_pub_authors" name="heal_pub_authors" value="<?= esc_attr($v('authors')) ?>" placeholder="Bovolini A, Makris KC"></td>
        </tr>
        <tr>
            <th scope="row"><label for="heal_pub_journal">Journal or conference</label></th>
            <td><input type="text" class="large-text" id="heal_pub_journal" name="heal_pub_journal" value="<?= esc_attr($v('journal')) ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="heal_pub_year">Year</label></th>
            <td><input type="number" id="heal_pub_year" name="heal_pub_year" min="2025" max="2100" value="<?= esc_attr($v('year')) ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="heal_pub_doi">DOI</label></th>
            <td>
                <input type="text" class="regular-text" id="heal_pub_doi" name="heal_pub_doi" value="<?= esc_attr($v('doi')) ?>"
                       pattern="(https?://(dx\.)?doi\.org/)?10\.\d{4,9}/\S+" placeholder="10.1234/abcd">
                <p class="description">Only the DOI (a pasted https://doi.org/… link is shortened for you).</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Open access</th>
            <td><label><input type="checkbox" name="heal_pub_oa" value="1" <?php checked($v('oa'), '1'); ?>> Free to read</label></td>
        </tr>
        <tr>
            <th scope="row"><label for="heal_pub_deliverable">Deliverable</label></th>
            <td>
                <select id="heal_pub_deliverable" name="heal_pub_deliverable">
                    <option value="">None</option>
                    <?php foreach (heal_public_deliverables() as $d) : ?>
                        <option value="<?= esc_attr($d['id']) ?>" <?php selected($v('deliverable'), $d['id']); ?>><?= esc_html($d['id'] . ' – ' . $d['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

add_action('save_post_heal_publication', function (int $postId) {
    $nonce = isset($_POST['heal_publication_nonce']) ? sanitize_text_field(wp_unslash($_POST['heal_publication_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'heal_publication_save')) {
        return;
    }
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $postId)) {
        return;
    }
    $text = fn(string $key): string => sanitize_text_field(wp_unslash($_POST[$key] ?? ''));

    update_post_meta($postId, '_heal_pub_authors', $text('heal_pub_authors'));
    update_post_meta($postId, '_heal_pub_journal', $text('heal_pub_journal'));

    $year = (int) $text('heal_pub_year');
    update_post_meta($postId, '_heal_pub_year', ($year >= 2025 && $year <= 2100) ? (string) $year : '');

    $doi = preg_replace('#^https?://(dx\.)?doi\.org/#i', '', trim($text('heal_pub_doi')));
    update_post_meta($postId, '_heal_pub_doi', preg_match(HEAL_DOI_PATTERN, $doi) ? $doi : '');

    update_post_meta($postId, '_heal_pub_oa', !empty($_POST['heal_pub_oa']) ? '1' : '');

    $allowed = array_column(heal_public_deliverables(), 'id');
    $deliverable = $text('heal_pub_deliverable');
    update_post_meta($postId, '_heal_pub_deliverable', in_array($deliverable, $allowed, true) ? $deliverable : '');
});

/** Published publications, newest year first. */
function heal_publications(): array
{
    $posts = get_posts([
        'post_type'      => 'heal_publication',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);

    $list = [];
    foreach ($posts as $p) {
        $m = fn(string $key): string => (string) get_post_meta($p->ID, '_heal_pub_' . $key, true);
        $list[] = [
            'title'       => get_the_title($p),
            'authors'     => $m('authors'),
            'journal'     => $m('journal'),
            'year'        => (int) $m('year'),
            'doi'         => $m('doi'),
            'oa'          => $m('oa') === '1',
            'deliverable' => $m('deliverable'),
        ];
    }
    usort($list, fn($a, $b) => $b['year'] <=> $a['year'] ?: strcmp($a['title'], $b['title']));
    return $list;
}

function heal_publications_html(): string
{
    $list = heal_publications();
    if (!$list) {
        return '<p>No publications yet. Two manuscripts are planned (deliverable D11, project month 22).</p>';
    }

    $html = '<ul class="pub-list">';
    foreach ($list as $p) {
        $html .= '<li class="pub">';
        if ($p['authors'] !== '') {
            // "Authors (2027). Title." or, without a year, "Authors. Title."
            $html .= '<span class="pub__authors">' . esc_html($p['authors']) . '</span>' . ($p['year'] ? ' ' : '. ');
        }
        if ($p['year']) {
            $html .= '(<span class="pub__year">' . (int) $p['year'] . '</span>). ';
        }
        $html .= '<span class="pub__title">' . esc_html($p['title']) . '</span>.';
        if ($p['journal'] !== '') {
            $html .= ' <em class="pub__journal">' . esc_html($p['journal']) . '</em>.';
        }
        if ($p['doi'] !== '') {
            $html .= ' <a class="pub__doi" href="' . esc_url('https://doi.org/' . $p['doi']) . '">https://doi.org/' . esc_html($p['doi']) . '</a>';
        }
        if ($p['oa']) {
            $html .= ' <span class="badge">Open access</span>';
        }
        $html .= '</li>';
    }
    return $html . '</ul>';
}

add_shortcode('heal_publications', fn() => heal_publications_html());
