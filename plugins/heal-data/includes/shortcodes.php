<?php
/**
 * Shortcodes. Put them on a page (block editor: "Shortcode" block; Elementor: "Shortcode" widget).
 *   [heal_results]     public deliverables table   (was results.astro)
 *   [heal_work_plan]   see workplan.php
 *   [heal_consortium]  partners                    (was consortium.astro)
 *   [heal_project_number], [heal_contact_email]  small facts for footer / contact page
 * HTML only, no styling: CSS is the theme's job. Classes start with "heal-".
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('heal_results', function () {
    $currentMonth = heal_project_month(heal_start_date());

    ob_start(); ?>
    <table class="heal-results">
        <thead>
            <tr>
                <th scope="col">Deliverable</th>
                <th scope="col">Title</th>
                <th scope="col">Due</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (heal_public_deliverables() as $d) :
            $status = heal_deliverable_status($d, $currentMonth); ?>
            <tr>
                <td><?= esc_html($d['id']) ?></td>
                <td><?= esc_html($d['title']) ?></td>
                <td>M<?= (int) $d['dueMonth'] ?></td>
                <td>
                    <span class="heal-status heal-status--<?= esc_attr($status) ?>"><?= esc_html(HEAL_STATUS_LABEL[$status]) ?></span>
                    <?php if (!empty($d['file'])) : ?>
                        · <a href="<?= esc_url($d['file']) ?>">Download PDF</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="heal-results__heading">Publications</h2>
    <?= function_exists('heal_publications_html') ? heal_publications_html() : '' ?>
    <?php return ob_get_clean();
});

// [heal_work_plan] now lives in workplan.php together with the timeline and the work package pages.

add_shortcode('heal_consortium', function () {
    ob_start(); ?>
    <div class="heal-partners">
    <?php foreach (heal_partners() as $p) : ?>
        <article class="heal-partner">
            <?php $name = esc_html($p['name']) . ' (' . esc_html($p['shortName']) . ')'; ?>
            <h3><?= !empty($p['website']) ? '<a href="' . esc_url($p['website']) . '">' . $name . '</a>' : $name ?></h3>
            <p class="heal-partner__role"><?= esc_html($p['role']) ?></p>
            <p><?= esc_html($p['description']) ?></p>
        </article>
    <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
});

add_shortcode('heal_project_number', fn() => esc_html(HEAL_SITE['projectNumber']));

add_shortcode('heal_contact_email', function () {
    if (!heal_has_contact_email()) {
        return 'The project email address will be added soon.'; // until config.php has the real address
    }
    $email = HEAL_SITE['contactEmail'];
    return sprintf('<a href="mailto:%1$s">%1$s</a>', esc_html($email));
});

/**
 * [heal_logos]: the HEAL logo variants with download links (Media kit page).
 * Files: assets/brand/heal-logo-<variant>.png and heal-logo-<variant>.svg.
 * Add a variant by adding its files and a line below ("dark": show it on a dark tile).
 */
add_shortcode('heal_logos', function () {
    $variants = [
        'orange'     => ['label' => 'Orange (main logo)', 'dark' => false],
        'negative'   => ['label' => 'Negative (blue)', 'dark' => false],
        'monochrome' => ['label' => 'Monochrome (grey)', 'dark' => false],
        'white'      => ['label' => 'White (for dark backgrounds)', 'dark' => true],
    ];
    $dir  = plugin_dir_path(__DIR__) . 'assets/brand/';
    $base = dirname(__DIR__) . '/heal-data.php';

    ob_start(); ?>
    <div class="logos">
    <?php foreach ($variants as $key => $v) :
        $png = "heal-logo-$key.png";
        $svg = "heal-logo-$key.svg";
        if (!is_readable($dir . $png)) {
            continue;
        }
        $url    = plugins_url('assets/brand/' . $png, $base);
        $size   = getimagesize($dir . $png);
        $hasSvg = is_readable($dir . $svg); ?>
        <figure class="logo-card">
            <div class="logo-card__img<?= $v['dark'] ? ' logo-card__img--dark' : '' ?>">
                <img src="<?= esc_url($hasSvg ? plugins_url('assets/brand/' . $svg, $base) : $url) ?>"
                     alt="HEAL logo, <?= esc_attr(strtolower($v['label'])) ?>" width="<?= (int) ($size[0] ?? 0) ?>" height="<?= (int) ($size[1] ?? 0) ?>" loading="lazy">
            </div>
            <figcaption>
                <?= esc_html($v['label']) ?><br>
                <span class="small">SVG (vector) and PNG, <?= (int) ($size[0] ?? 0) ?> × <?= (int) ($size[1] ?? 0) ?> px, transparent background</span><br>
                <?php if ($hasSvg) : ?>
                    <a href="<?= esc_url(plugins_url('assets/brand/' . $svg, $base)) ?>" download>Download SVG</a> ·
                <?php endif; ?>
                <a href="<?= esc_url($url) ?>" download>Download PNG</a>
            </figcaption>
        </figure>
    <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
});
