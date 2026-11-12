<?php
/** Deliverable status. Port of src/lib/deliverableStatus.ts. */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_STATUS_LABEL = [
    'planned'     => 'Planned',
    'in-progress' => 'In progress',
    'published'   => 'Published',
];

/**
 * Strict YYYY-MM-DD. Returns null for anything else, including impossible dates such as 2026-02-31
 * (PHP would otherwise quietly turn that into 3 March).
 */
function heal_parse_date(?string $value): ?DateTimeImmutable
{
    if (!$value) {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return ($date && $date->format('Y-m-d') === $value) ? $date : null;
}

/** Current project month (M1 = first month). Null if there is no start date or the project has not started. */
function heal_project_month(?string $startDate, ?DateTimeImmutable $today = null): ?int
{
    $start = heal_parse_date($startDate);
    if (!$start) {
        return null;
    }
    $today ??= new DateTimeImmutable('today', wp_timezone());

    $months = ((int) $today->format('Y') - (int) $start->format('Y')) * 12
        + ((int) $today->format('n') - (int) $start->format('n'));
    if ((int) $today->format('j') < (int) $start->format('j')) {
        $months -= 1;
    }
    return $months < 0 ? null : $months + 1;
}

/**
 * Status of a deliverable:
 * 1. manual override in the data wins;
 * 2. a file exists → published;
 * 3. due month reached → in progress (being finalised);
 * 4. otherwise → planned.
 */
function heal_deliverable_status(array $d, ?int $currentMonth): string
{
    if (!empty($d['status']) && isset(HEAL_STATUS_LABEL[$d['status']])) {
        return $d['status'];
    }
    if (!empty($d['file'])) {
        return 'published';
    }
    if ($currentMonth !== null && $currentMonth >= $d['dueMonth']) {
        return 'in-progress';
    }
    return 'planned';
}
