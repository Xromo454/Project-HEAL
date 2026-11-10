<?php
/**
 * Gallery. Port of gallery.astro + GalleryItem.astro.
 * Photos: data/gallery/*.json, image files in data/gallery/images/.
 * Shortcode: [heal_gallery]
 */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_GALLERY_SECTIONS = [
    'moodboard' => ['Moodboard', 'The visual direction of HEAL.', 'Colours, textures and references that shaped the look of the project.'],
    'stock'     => ['Stock photos', 'Photos used on this website.', 'Stock images used across the site, with credit to their photographers.'],
    'limassol'  => ['Limassol', 'The Limassol living lab.', 'Streets, routes and places in Limassol, where the HEAL pilot takes place.'],
    'locations' => ['Other locations', 'Partner sites and branches.', 'Photos from the other sites of the organisations involved, by organisation and place.'],
];

/** All valid photos, sorted by order, then file name. Same checks as the Astro schema. */
function heal_gallery_photos(): array
{
    static $photos = null;
    if ($photos !== null) {
        return $photos;
    }
    $photos = [];
    $dir = HEAL_DATA_DIR . 'gallery/';
    foreach (glob($dir . '*.json') ?: [] as $file) {
        $p = json_decode((string) file_get_contents($file), true);
        if (!is_array($p) || empty($p['image']) || empty($p['alt']) || !isset(HEAL_GALLERY_SECTIONS[$p['section'] ?? ''])) {
            continue;
        }
        if ($p['section'] === 'stock' && empty($p['credit'])) {
            continue; // stock photos need a credit
        }
        if ($p['section'] === 'locations' && (empty($p['organisation']) || empty($p['location']))) {
            continue;
        }
        $img = ltrim(preg_replace('#^\./#', '', $p['image']), '/');
        if (!is_readable($dir . $img)) {
            continue;
        }
        $p['id']    = basename($file, '.json');
        $p['url']   = plugins_url('data/gallery/' . $img, dirname(__DIR__) . '/heal-data.php');
        $p['order'] = (int) ($p['order'] ?? 0);
        $size = @getimagesize($dir . $img);
        $p['width']  = $size[0] ?? null;
        $p['height'] = $size[1] ?? null;
        $photos[] = $p;
    }
    usort($photos, fn($a, $b) => $a['order'] <=> $b['order'] ?: strcmp($a['id'], $b['id']));
    return $photos;
}

function heal_gallery_item(array $p): string
{
    $dims = $p['width'] ? sprintf(' width="%d" height="%d"', $p['width'], $p['height']) : '';
    $html = '<figure class="gallery-item"><a class="gallery-item__link" href="' . esc_url($p['url']) . '">'
        . '<img src="' . esc_url($p['url']) . '" alt="' . esc_attr($p['alt']) . '"' . $dims . ' loading="lazy" decoding="async"></a>';

    $caption = $p['caption'] ?? '';
    $credit  = $p['credit'] ?? '';
    if ($caption || $credit) {
        $html .= '<figcaption>' . esc_html($caption);
        if ($credit) {
            $who = !empty($p['creditUrl'])
                ? '<a href="' . esc_url($p['creditUrl']) . '">' . esc_html($credit) . '</a>'
                : esc_html($credit);
            $html .= '<span class="gallery-item__credit">Photo: ' . $who . '</span>';
        }
        $html .= '</figcaption>';
    }
    return $html . '</figure>';
}

function heal_gallery_grid(array $list, bool $wide = false): string
{
    $html = '<ul class="gallery-grid' . ($wide ? ' gallery-grid--wide' : '') . '" role="list">';
    foreach ($list as $p) {
        $html .= '<li>' . heal_gallery_item($p) . '</li>';
    }
    return $html . '</ul>';
}

add_shortcode('heal_gallery', function () {
    $photos   = heal_gallery_photos();
    $partners = heal_partners();
    $bySection = fn($id) => array_values(array_filter($photos, fn($p) => $p['section'] === $id));

    ob_start(); ?>
    <nav class="gallery-jump" aria-label="Gallery sections">
        <div class="container">
            <ul role="list">
            <?php foreach (HEAL_GALLERY_SECTIONS as $id => [$label]) : ?>
                <li><a href="#<?= esc_attr($id) ?>"><?= esc_html($label) ?> <span class="gallery-jump__count"><?= count($bySection($id)) ?></span></a></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <?php $i = 0;
    foreach (HEAL_GALLERY_SECTIONS as $id => [$label, $title, $text]) :
        $list = $bySection($id); ?>
        <section class="section<?= $i % 2 === 0 ? ' section--alt' : '' ?>" id="<?= esc_attr($id) ?>" aria-labelledby="<?= esc_attr($id) ?>-title">
            <div class="container">
                <span class="eyebrow"><?= sprintf('%02d', ++$i) ?> / <?= esc_html($label) ?></span>
                <div class="split section-head">
                    <h2 id="<?= esc_attr($id) ?>-title"><?= esc_html($title) ?></h2>
                    <p><?= esc_html($text) ?></p>
                </div>

                <?php if (!$list) : ?>
                    <div class="panel panel--narrow">
                        <span class="pill">Photos coming soon</span>
                        <p>No photos have been added to this section yet.</p>
                    </div>
                <?php elseif ($id === 'locations') :
                    // organisation → place → photos
                    $groups = [];
                    foreach ($list as $p) {
                        $org = $partners[$p['organisation']]['name'] ?? 'Other';
                        $groups[$org][$p['location']][] = $p;
                    }
                    foreach ($groups as $org => $places) : ?>
                        <div class="gallery-group">
                            <h3 class="gallery-group__title"><?= esc_html($org) ?></h3>
                            <?php foreach ($places as $place => $items) : ?>
                                <h4 class="eyebrow eyebrow--navy gallery-group__place"><?= esc_html($place) ?></h4>
                                <?= heal_gallery_grid($items) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach;
                else :
                    echo heal_gallery_grid($list, $id === 'moodboard');
                endif; ?>
            </div>
        </section>
    <?php endforeach;
    return ob_get_clean();
});
