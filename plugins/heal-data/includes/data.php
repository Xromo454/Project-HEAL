<?php
/** Loads the JSON data. Port of src/content.config.ts (loaders + the key checks). */

if (!defined('ABSPATH')) {
    exit;
}

/** Reads data/<name>.json once per request. Returns [] if the file is missing or broken. */
function heal_load(string $name): array
{
    static $cache = [];
    if (!isset($cache[$name])) {
        $json = @file_get_contents(HEAL_DATA_DIR . $name . '.json');
        $cache[$name] = $json ? (json_decode($json, true) ?: []) : [];
    }
    return $cache[$name];
}

/** Public deliverables only, sorted by due month. Confidential ones never reach the page, even if the JSON is wrong. */
function heal_public_deliverables(): array
{
    $list = array_filter(
        heal_load('deliverables'),
        fn($d) => !empty($d['public']) && in_array($d['id'], HEAL_PUBLIC_DELIVERABLES, true)
    );
    usort($list, fn($a, $b) => $a['dueMonth'] <=> $b['dueMonth']);
    return $list;
}

function heal_work_packages(): array
{
    $list = heal_load('workPackages');
    usort($list, fn($a, $b) => strcmp($a['id'], $b['id']));
    return $list;
}

/** Milestones of one work package, sorted by month. */
function heal_milestones_for(string $wpId): array
{
    $list = array_filter(heal_load('milestones'), fn($m) => $m['workPackage'] === $wpId);
    usort($list, fn($a, $b) => $a['month'] <=> $b['month']);
    return $list;
}

/** Partners from data/partners/*.json, keyed by file name (e.g. "cut"). Sorted Host → Partner → End user. */
function heal_partners(): array
{
    static $partners = null;
    if ($partners === null) {
        $partners = [];
        foreach (glob(HEAL_DATA_DIR . 'partners/*.json') ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data)) {
                $partners[basename($file, '.json')] = $data;
            }
        }
        $order = ['Host Organisation', 'Partner', 'End user / stakeholder'];
        uasort($partners, fn($a, $b) => array_search($a['role'], $order) <=> array_search($b['role'], $order));
    }
    return $partners;
}
