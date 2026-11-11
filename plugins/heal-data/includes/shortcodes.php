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
    $email = HEAL_SITE['contactEmail'];
    return sprintf('<a href="mailto:%1$s">%1$s</a>', esc_html($email));
});
