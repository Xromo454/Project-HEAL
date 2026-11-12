<?php
/**
 * Tests for the logic of the HEAL Data plugin that needs no WordPress:
 *   - project month and deliverable status   (includes/status.php)
 *   - cleaning of DOIs                       (includes/data.php)
 * No test framework needed:   php scripts/test.php
 * Runs in CI on every push (.github/workflows/ci.yml). Exit code 1 if anything fails.
 *
 * "today" is always passed in, so the results do not depend on the day the test runs.
 */

define('ABSPATH', __DIR__);

// The only WordPress function status.php uses when "today" is not passed. Not used here, but defined to be safe.
function wp_timezone(): DateTimeZone
{
    return new DateTimeZone('UTC');
}

require __DIR__ . '/../plugins/heal-data/includes/status.php';
require __DIR__ . '/../plugins/heal-data/includes/data.php';

$passed = 0;
$failed = [];

function check(string $name, $actual, $expected): void
{
    global $passed, $failed;
    if ($actual === $expected) {
        $passed++;
        return;
    }
    $failed[] = sprintf("  FAIL %s\n       expected %s, got %s", $name, var_export($expected, true), var_export($actual, true));
}

/** Month for a start date and a "today" given as strings. */
function month(?string $start, string $today): ?int
{
    return heal_project_month($start, new DateTimeImmutable($today));
}

/* ---------- Project month ---------- */

check('no start date', month(null, '2026-11-11'), null);
check('empty start date', month('', '2026-11-11'), null);
check('start date is not a date', month('hello', '2026-11-11'), null);
check('wrong format (dd-mm-yyyy)', month('01-03-2026', '2026-11-11'), null);

check('first day of the project is M1', month('2026-03-15', '2026-03-15'), 1);
check('last day of month 1 is still M1', month('2026-03-15', '2026-04-14'), 1);
check('same day one month later is M2', month('2026-03-15', '2026-04-15'), 2);
check('before the start: not started', month('2026-03-15', '2026-03-14'), null);
check('a year before: not started', month('2026-03-15', '2025-03-15'), null);

check('start on the 1st, today the 31st of the same month', month('2026-01-01', '2026-01-31'), 1);
check('start on the 1st, first day of the next month', month('2026-01-01', '2026-02-01'), 2);

check('start on the 31st: end of February is still M1', month('2026-01-31', '2026-02-28'), 1);
check('start on the 31st: 31 March is M3', month('2026-01-31', '2026-03-31'), 3);

check('across a year change', month('2025-11-10', '2026-11-09'), 12);
check('across a year change, same day', month('2025-11-10', '2026-11-10'), 13);

check('leap day start, a year later (28 Feb)', month('2024-02-29', '2025-02-28'), 12);
check('leap day start, 1 March', month('2024-02-29', '2025-03-01'), 13);

check('last day of month 24 is M24', month('2025-01-01', '2026-12-31'), 24);
check('after the 24 months the number keeps counting (M25)', month('2025-01-01', '2027-01-01'), 25);

check('the real case on 2026-11-11 with start 2025-01-01 is M23', month('2025-01-01', '2026-11-11'), 23);

// An impossible date must not be accepted and quietly turned into another date.
check('31 February is not a date', month('2026-02-31', '2026-11-11'), null);
check('month 13 is not a date', month('2026-13-01', '2026-11-11'), null);
check('day 0 is not a date', month('2026-03-00', '2026-11-11'), null);

/* ---------- Deliverable status ---------- */

$d = ['dueMonth' => 6];

check('no month yet: planned', heal_deliverable_status($d, null), 'planned');
check('month before the due month: planned', heal_deliverable_status($d, 5), 'planned');
check('due month reached: in progress', heal_deliverable_status($d, 6), 'in-progress');
check('past the due month without a file: in progress', heal_deliverable_status($d, 20), 'in-progress');

check('a file means published', heal_deliverable_status($d + ['file' => '/x.pdf'], null), 'published');
check('a file means published, even before the due month', heal_deliverable_status($d + ['file' => '/x.pdf'], 1), 'published');
check('an empty file field is ignored', heal_deliverable_status($d + ['file' => ''], 3), 'planned');

check('manual status wins over the month', heal_deliverable_status($d + ['status' => 'published'], 1), 'published');
check('manual status wins over a file', heal_deliverable_status($d + ['status' => 'planned', 'file' => '/x.pdf'], 9), 'planned');
check('manual "in-progress" is kept', heal_deliverable_status($d + ['status' => 'in-progress'], null), 'in-progress');
check('an unknown manual status is ignored', heal_deliverable_status($d + ['status' => 'banana'], 3), 'planned');

foreach (['planned', 'in-progress', 'published'] as $s) {
    check("label exists for $s", isset(HEAL_STATUS_LABEL[$s]) && HEAL_STATUS_LABEL[$s] !== '', true);
}

/* ---------- The five public deliverables (B4.4) with the demo start date ---------- */

$deliverables = json_decode((string) file_get_contents(__DIR__ . '/../plugins/heal-data/data/deliverables.json'), true);
$public = array_values(array_filter($deliverables, fn($x) => !empty($x['public'])));
check('five public deliverables in the data', array_column($public, 'id'), ['D4', 'D10', 'D11', 'D12', 'D14']);

$m23 = month('2025-01-01', '2026-11-11'); // M23
$status = [];
foreach ($public as $x) {
    $status[$x['id']] = heal_deliverable_status($x, $m23);
}
check('statuses in month 23', $status, [
    'D4'  => 'in-progress', // due M6
    'D10' => 'in-progress', // due M18
    'D11' => 'in-progress', // due M22
    'D12' => 'planned',     // due M24
    'D14' => 'planned',     // due M24
]);

$none = [];
foreach ($public as $x) {
    $none[$x['id']] = heal_deliverable_status($x, month(null, '2026-11-11'));
}
check('without a start date everything is planned', array_unique(array_values($none)), ['planned']);

/* ---------- DOI cleaning ---------- */

check('plain DOI is kept', heal_clean_doi('10.1234/abcd'), '10.1234/abcd');
check('DOI with dots and dashes', heal_clean_doi('10.1016/j.jhealth-2027.05.001'), '10.1016/j.jhealth-2027.05.001');
check('surrounding spaces are removed', heal_clean_doi("  10.1234/abcd \n"), '10.1234/abcd');
check('https://doi.org/ link is shortened', heal_clean_doi('https://doi.org/10.1234/abcd'), '10.1234/abcd');
check('http://dx.doi.org/ link is shortened', heal_clean_doi('http://dx.doi.org/10.1234/abcd.5678'), '10.1234/abcd.5678');
check('HTTPS in capitals is shortened too', heal_clean_doi('HTTPS://DOI.ORG/10.1234/abcd'), '10.1234/abcd');
check('"doi:" prefix is removed', heal_clean_doi('doi:10.1234/abcd'), '10.1234/abcd');
check('"DOI: " prefix with a space is removed', heal_clean_doi('DOI: 10.1234/abcd'), '10.1234/abcd');

check('empty', heal_clean_doi(''), '');
check('not a DOI', heal_clean_doi('not-a-doi'), '');
check('a normal web address is not a DOI', heal_clean_doi('https://example.com/paper'), '');
check('too few digits after 10.', heal_clean_doi('10.12/abc'), '');
check('nothing after the slash', heal_clean_doi('10.1234/'), '');
check('space inside', heal_clean_doi('10.1234/ab cd'), '');

/* ---------- Report ---------- */

$total = $passed + count($failed);
if ($failed) {
    echo implode("\n", $failed) . "\n";
    echo "\n" . count($failed) . " of $total checks FAILED\n";
    exit(1);
}
echo "All $total checks passed\n";
