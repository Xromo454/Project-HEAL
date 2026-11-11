<?php
/**
 * Work plan views, built from data/workPackages.json, milestones.json and deliverables.json:
 *
 *   [heal_timeline]      the 24 months as bars: one row per work package, milestones marked with diamonds,
 *                        and a line for the current month once the project start date is set
 *   [heal_work_plan]     the five work packages as cards, each linking to its own page
 *   [heal_wp id="WP1"]   the page of one work package: objective, tasks on a timeline, milestones,
 *                        public deliverables (pages /work-plan/wp1/ ... /work-plan/wp5/)
 *
 * The bars are decorative (aria-hidden): the text next to them carries the same information.
 */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_MONTHS = 24;

function heal_wp_url(string $id): string
{
    return home_url('/work-plan/' . strtolower($id) . '/');
}

function heal_months(int $start, int $end): string
{
    return $start === $end ? "M$start" : "M{$start}–M{$end}";
}

function heal_find_wp(string $id): ?array
{
    foreach (heal_work_packages() as $wp) {
        if ($wp['id'] === $id) {
            return $wp;
        }
    }
    return null;
}

/** The current project month (1-24) or null while the start date is not set. */
function heal_now_month(): ?int
{
    $m = heal_project_month(heal_start_date());
    return ($m !== null && $m >= 1 && $m <= HEAL_MONTHS) ? $m : null;
}

/** One bar on the 24-month track, with optional milestone diamonds. */
function heal_track(int $start, int $end, array $milestones = []): string
{
    $html = '<div class="gantt__track" aria-hidden="true">'
        . '<span class="gantt__bar" style="--s:' . $start . ';--e:' . $end . '"></span>';
    foreach ($milestones as $m) {
        $html .= '<span class="gantt__ms" style="--m:' . (int) $m['month'] . '" title="'
            . esc_attr($m['id'] . ' (M' . (int) $m['month'] . '): ' . $m['title']) . '"></span>';
    }
    if (($now = heal_now_month()) !== null) {
        $html .= '<span class="gantt__now" style="--m:' . $now . '"></span>';
    }
    return $html . '</div>';
}

function heal_axis(): string
{
    $html = '<div class="gantt__axis" aria-hidden="true">';
    foreach ([1, 6, 12, 18, 24] as $m) {
        $html .= '<span style="--m:' . $m . '">M' . $m . '</span>';
    }
    return $html . '</div>';
}

add_shortcode('heal_timeline', function () {
    $now = heal_now_month();

    ob_start(); ?>
    <figure class="gantt">
        <figcaption>
            The five work packages over the 24 months of the project (M1 is the first month).
            Diamonds mark milestones<?= $now ? '; the line marks the current month (M' . (int) $now . ')' : '' ?>.
        </figcaption>
        <div class="gantt__row gantt__row--axis"><span></span><?= heal_axis() ?></div>
        <ol class="gantt__rows">
        <?php foreach (heal_work_packages() as $wp) :
            $ms = heal_milestones_for($wp['id']); ?>
            <li class="gantt__row">
                <a class="gantt__label" href="<?= esc_url(heal_wp_url($wp['id'])) ?>">
                    <strong><?= esc_html($wp['id']) ?></strong> <?= esc_html($wp['title']) ?>
                    <span class="gantt__months"><?= esc_html(heal_months($wp['startMonth'], $wp['endMonth'])) ?></span>
                </a>
                <?= heal_track($wp['startMonth'], $wp['endMonth'], $ms) ?>
                <?php if ($ms) : ?>
                    <span class="visually-hidden">Milestones: <?= esc_html(implode(', ', array_map(fn($m) => $m['id'] . ' in month ' . $m['month'], $ms))) ?>.</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ol>
    </figure>
    <?php return ob_get_clean();
});

add_shortcode('heal_work_plan', function () {
    $partners = heal_partners();

    ob_start(); ?>
    <div class="wp-cards">
    <?php foreach (heal_work_packages() as $wp) :
        $lead = $partners[$wp['lead']]['shortName'] ?? $wp['lead'];
        $n = count(heal_milestones_for($wp['id'])); ?>
        <article class="wp-card" id="<?= esc_attr(strtolower($wp['id'])) ?>">
            <p class="eyebrow"><?= esc_html($wp['id']) ?> · <?= esc_html(heal_months($wp['startMonth'], $wp['endMonth'])) ?> · Lead: <?= esc_html($lead) ?></p>
            <h3><a href="<?= esc_url(heal_wp_url($wp['id'])) ?>"><?= esc_html($wp['title']) ?></a></h3>
            <p><?= esc_html($wp['objective']) ?></p>
            <p class="wp-card__meta"><?= count($wp['tasks']) ?> tasks · <?= $n ?> milestone<?= $n === 1 ? '' : 's' ?></p>
            <a class="text-link" href="<?= esc_url(heal_wp_url($wp['id'])) ?>">Tasks and milestones <span class="arrow" aria-hidden="true">↗</span></a>
        </article>
    <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
});

add_shortcode('heal_wp', function ($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'heal_wp');
    $wp = heal_find_wp(strtoupper(trim($atts['id'])));
    if (!$wp) {
        return '';
    }

    $all      = heal_work_packages();
    $pos      = (int) array_search($wp['id'], array_column($all, 'id'), true);
    $prev     = $all[$pos - 1] ?? null;
    $next     = $all[$pos + 1] ?? null;
    $partners = heal_partners();
    $lead     = $partners[$wp['lead']]['name'] ?? $wp['lead'];
    $ms       = heal_milestones_for($wp['id']);
    $current  = heal_now_month();
    $deliv    = array_filter(heal_public_deliverables(), fn($d) => $d['workPackage'] === $wp['id']);

    ob_start(); ?>
    <p class="wp-detail__crumb"><a href="<?= esc_url(home_url('/work-plan/')) ?>">← Work plan</a></p>
    <p class="wp-detail__meta"><?= esc_html(heal_months($wp['startMonth'], $wp['endMonth'])) ?> · Lead: <?= esc_html($lead) ?></p>
    <p class="lead"><?= esc_html($wp['objective']) ?></p>

    <h2>Tasks</h2>
    <div class="gantt gantt--tasks">
        <div class="gantt__row gantt__row--axis"><span></span><?= heal_axis() ?></div>
        <ol class="gantt__rows">
        <?php foreach ($wp['tasks'] as $t) : ?>
            <li class="gantt__row">
                <span class="gantt__label">
                    <strong><?= esc_html($t['id']) ?></strong> <?= esc_html($t['title']) ?>
                    <span class="gantt__months"><?= esc_html(heal_months($t['startMonth'], $t['endMonth'])) ?></span>
                </span>
                <?= heal_track($t['startMonth'], $t['endMonth']) ?>
            </li>
        <?php endforeach; ?>
        </ol>
    </div>

    <?php if ($ms) : ?>
        <h2>Milestones</h2>
        <ul class="wp-milestones">
        <?php foreach ($ms as $m) : ?>
            <li>
                <strong><?= esc_html($m['id']) ?></strong> · month <?= (int) $m['month'] ?>: <?= esc_html($m['title']) ?>
                <?php if (!empty($m['meansOfVerification'])) : ?>
                    <br><span class="small">How it is verified: <?= esc_html($m['meansOfVerification']) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($deliv) : ?>
        <h2>Public deliverables</h2>
        <ul class="wp-milestones">
        <?php foreach ($deliv as $d) :
            $status = heal_deliverable_status($d, heal_project_month(heal_start_date())); ?>
            <li>
                <strong><?= esc_html($d['id']) ?></strong> · due M<?= (int) $d['dueMonth'] ?>: <?= esc_html($d['title']) ?>
                <span class="heal-status heal-status--<?= esc_attr($status) ?>"><?= esc_html(HEAL_STATUS_LABEL[$status]) ?></span>
            </li>
        <?php endforeach; ?>
        </ul>
        <p class="small">All public deliverables are listed on the <a href="<?= esc_url(home_url('/results/')) ?>">Results</a> page.</p>
    <?php endif; ?>

    <nav class="wp-nav" aria-label="Work packages">
        <span><?php if ($prev) : ?><a href="<?= esc_url(heal_wp_url($prev['id'])) ?>">← <?= esc_html($prev['id']) ?>: <?= esc_html($prev['title']) ?></a><?php endif; ?></span>
        <span><?php if ($next) : ?><a href="<?= esc_url(heal_wp_url($next['id'])) ?>"><?= esc_html($next['id']) ?>: <?= esc_html($next['title']) ?> →</a><?php endif; ?></span>
    </nav>
    <?php return ob_get_clean();
});
