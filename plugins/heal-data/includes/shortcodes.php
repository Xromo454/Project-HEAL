<?php
/**
 * Shortcodes. Put them on a page (block editor: "Shortcode" block; Elementor: "Shortcode" widget).
 *   [heal_results]     public deliverables table   (was results.astro)
 *   [heal_work_plan]   work packages, tasks, milestones (was work-plan.astro)
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
    <?php return ob_get_clean();
});

add_shortcode('heal_work_plan', function () {
    $partners = heal_partners();

    ob_start();
    foreach (heal_work_packages() as $wp) :
        $lead = $partners[$wp['lead']]['shortName'] ?? $wp['lead']; ?>
        <section class="heal-wp" id="<?= esc_attr(strtolower($wp['id'])) ?>">
            <h2><?= esc_html($wp['id'] . ': ' . $wp['title']) ?></h2>
            <p class="heal-wp__meta">M<?= (int) $wp['startMonth'] ?>–M<?= (int) $wp['endMonth'] ?> · Lead: <?= esc_html($lead) ?></p>
            <p><?= esc_html($wp['objective']) ?></p>

            <h3>Tasks</h3>
            <ul class="heal-wp__tasks">
            <?php foreach ($wp['tasks'] as $t) : ?>
                <li><?= esc_html($t['id'] . ' ' . $t['title']) ?> (M<?= (int) $t['startMonth'] ?>–M<?= (int) $t['endMonth'] ?>)</li>
            <?php endforeach; ?>
            </ul>

            <?php $milestones = heal_milestones_for($wp['id']);
            if ($milestones) : ?>
                <h3>Milestones</h3>
                <ul class="heal-wp__milestones">
                <?php foreach ($milestones as $m) : ?>
                    <li><?= esc_html($m['id']) ?> (M<?= (int) $m['month'] ?>): <?= esc_html($m['title']) ?></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endforeach;
    return ob_get_clean();
});

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
