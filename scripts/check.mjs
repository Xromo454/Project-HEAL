#!/usr/bin/env node
// Project checks for the HEAL WordPress code. No dependencies: `node scripts/check.mjs`.
// Runs in CI on every push and pull request (.github/workflows/ci.yml) and works locally too.
//
// Errors fail the build. Warnings are only printed.
//   1. Data files (JSON) are valid and follow the rules of the Technical Annex (B4.2-B4.4)
//   2. Public deliverables are exactly D4, D10, D11, D12, D14 and confidential ones have no file (B4.4)
//   3. Page files hold balanced WordPress block markup and use only shortcodes that exist
//   4. Content rules from the sprint plan (no "3 months", no old deliverable numbers, ...)
//   5. Theme and plugin have the files WordPress needs

import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('..', import.meta.url));
const plugin = join(root, 'plugins/heal-data');
const theme = join(root, 'themes/heal');

const errors = [];
const warnings = [];
const fail = (file, msg) => errors.push(`${file}: ${msg}`);
const warn = (file, msg) => warnings.push(`${file}: ${msg}`);
const rel = (p) => relative(root, p).replaceAll('\\', '/');

const read = (p) => readFileSync(p, 'utf8');
const files = (dir, ext) => (existsSync(dir) ? readdirSync(dir).filter((f) => f.endsWith(ext)).sort() : []);

function json(p) {
  try {
    return JSON.parse(read(p));
  } catch (e) {
    fail(rel(p), `not valid JSON (${e.message})`);
    return null;
  }
}

const PUBLIC = ['D4', 'D10', 'D11', 'D12', 'D14']; // B4.4: every other deliverable is confidential
const month = (n) => Number.isInteger(n) && n >= 1 && n <= 24;
const WPS = ['WP1', 'WP2', 'WP3', 'WP4', 'WP5'];

// ---------- 1. Data ----------
const dataDir = join(plugin, 'data');

const partnerIds = files(join(dataDir, 'partners'), '.json').map((f) => f.replace('.json', ''));
const ROLES = ['Host Organisation', 'Partner', 'End user / stakeholder'];
for (const f of files(join(dataDir, 'partners'), '.json')) {
  const p = join(dataDir, 'partners', f);
  const d = json(p);
  if (!d) continue;
  for (const k of ['name', 'shortName', 'role', 'description']) if (!d[k]) fail(rel(p), `missing "${k}"`);
  if (d.role && !ROLES.includes(d.role)) fail(rel(p), `role must be one of: ${ROLES.join(', ')}`);
  if (d.website && !/^https?:\/\//.test(d.website)) fail(rel(p), 'website must be a full URL');
}

const wps = json(join(dataDir, 'workPackages.json')) ?? [];
const seenWp = new Set();
for (const wp of wps) {
  const where = `workPackages.json ${wp.id}`;
  if (!WPS.includes(wp.id)) fail(where, 'id must be WP1 to WP5');
  if (seenWp.has(wp.id)) fail(where, 'duplicate id');
  seenWp.add(wp.id);
  if (!partnerIds.includes(wp.lead)) fail(where, `lead "${wp.lead}" is not a file in data/partners`);
  if (!month(wp.startMonth) || !month(wp.endMonth)) fail(where, 'startMonth/endMonth must be 1 to 24');
  if (wp.endMonth < wp.startMonth) fail(where, 'endMonth is before startMonth');
  for (const t of wp.tasks ?? []) {
    if (!/^T[1-5]\.\d+$/.test(t.id)) fail(where, `task id "${t.id}" must look like T2.1`);
    if (!month(t.startMonth) || !month(t.endMonth) || t.endMonth < t.startMonth) fail(where, `task ${t.id}: check the months`);
  }
}
if (wps.length !== 5) fail('workPackages.json', `expected 5 work packages, found ${wps.length}`);

const milestones = json(join(dataDir, 'milestones.json')) ?? [];
const seenM = new Set();
for (const m of milestones) {
  const where = `milestones.json ${m.id}`;
  if (!/^M[1-5]\.\d+$/.test(m.id)) fail(where, 'id must look like M1.1');
  if (seenM.has(m.id)) fail(where, 'duplicate id');
  seenM.add(m.id);
  if (!WPS.includes(m.workPackage)) fail(where, `unknown work package "${m.workPackage}"`);
  if (!month(m.month)) fail(where, 'month must be 1 to 24');
}

const deliverables = json(join(dataDir, 'deliverables.json')) ?? [];
const seenD = new Set();
for (const d of deliverables) {
  const where = `deliverables.json ${d.id}`;
  if (!/^D(1[0-4]|[1-9])$/.test(d.id)) fail(where, 'id must be D1 to D14');
  if (seenD.has(d.id)) fail(where, 'duplicate id');
  seenD.add(d.id);
  if (!WPS.includes(d.workPackage)) fail(where, `unknown work package "${d.workPackage}"`);
  if (!month(d.dueMonth)) fail(where, 'dueMonth must be 1 to 24');
  if (d.public !== PUBLIC.includes(d.id)) fail(where, `"public" must be ${PUBLIC.includes(d.id)} (only ${PUBLIC.join(', ')} are public, B4.4)`);
  if (!d.public && d.file) fail(where, 'a confidential deliverable must not have a file');
}
if (deliverables.length !== 14) fail('deliverables.json', `expected 14 deliverables, found ${deliverables.length}`);

// The PHP constant must match the data.
const config = read(join(plugin, 'includes/config.php'));
const phpList = /HEAL_PUBLIC_DELIVERABLES\s*=\s*\[([^\]]*)\]/.exec(config)?.[1].match(/D\d+/g) ?? [];
if ([...phpList].sort().join() !== [...PUBLIC].sort().join()) {
  fail('includes/config.php', `HEAL_PUBLIC_DELIVERABLES is [${phpList.join(', ')}], expected [${PUBLIC.join(', ')}]`);
}

// Gallery starting photos
const SECTIONS = ['moodboard', 'stock', 'limassol', 'locations'];
for (const f of files(join(dataDir, 'gallery'), '.json')) {
  const p = join(dataDir, 'gallery', f);
  const g = json(p);
  if (!g) continue;
  if (!g.alt) fail(rel(p), 'missing alt text');
  if (!SECTIONS.includes(g.section)) fail(rel(p), `section must be one of: ${SECTIONS.join(', ')}`);
  if (g.section === 'stock' && !g.credit) fail(rel(p), 'stock photos need a credit');
  if (!g.image || !existsSync(join(dataDir, 'gallery', g.image))) fail(rel(p), `image file not found: ${g.image}`);
}

// ---------- 2. Page content ----------
const pagesDir = join(plugin, 'content/pages');
const shortcodesDefined = new Set(
  [...readdirSync(join(plugin, 'includes')).flatMap((f) => [...read(join(plugin, 'includes', f)).matchAll(/add_shortcode\('([a-z_]+)'/g)].map((m) => m[1]))],
);

// Every page in pages.php has a content file (and the other way round).
const pageSlugs = [
  ...read(join(plugin, 'includes/pages.php')).matchAll(/^\s+'([a-z0-9-]+)'\s+=>\s+\['/gm),
].map((m) => m[1]);
// One page per work package (wp1 ... wp5) is added by heal_page_list() from the data.
for (const wp of wps) pageSlugs.push(wp.id.toLowerCase());

// Each work package page file shows its own work package.
for (const wp of wps) {
  const f = join(pagesDir, `${wp.id.toLowerCase()}.html`);
  if (existsSync(f) && !read(f).includes(`[heal_wp id="${wp.id}"]`)) fail(rel(f), `must contain [heal_wp id="${wp.id}"]`);
}
const pageFiles = files(pagesDir, '.html').map((f) => f.replace('.html', ''));
for (const s of pageSlugs) if (!pageFiles.includes(s)) fail('includes/pages.php', `page "${s}" has no file content/pages/${s}.html`);
for (const f of pageFiles) if (!pageSlugs.includes(f)) warn(`content/pages/${f}.html`, 'is not listed in heal_page_list() (pages.php), so it is never created');

// Content rules (sprint plan, section 1). Hard rules fail, softer ones only warn.
const RULES = [
  { re: /\b(3|three)[- ]months?\b/i, msg: 'the intervention lasts 6 months (M10-M15); "3 months" is a leftover in the annex', error: true },
  { re: /\bD15\b|\bD16\b|\bD2\.[12]\b|\bD3\.4\b/, msg: 'old deliverable number; use D1 to D14 from table B4.4', error: true },
  { re: /\b7\.4\.3\b/, msg: 'S3Cy area is 7.4.2 (the only box ticked in the annex)', error: true },
  { re: /Nicolaides|Nikolaidis/, msg: 'spelling of the NextBike lead is not confirmed; do not publish the name yet', error: true },
  { re: /Cyprus Bioethics Committee|CUT Ethics Committee/, msg: 'name the ethics committee only once it is decided', error: true },
  { re: /Limassol Municipality(?![^.]*\b(?:not|neither)\b)[^.]{0,80}\b(partner|beneficiary)\b/i, msg: 'Limassol Municipality is an end user / stakeholder, not a partner or beneficiary', error: true },
  { re: /\bwill (reduce|improve|increase|prove|show|cut|save)\b/i, msg: 'avoid claims of effect before results exist; use "aims to"', error: false },
];

for (const f of files(pagesDir, '.html')) {
  const p = join(pagesDir, f);
  const text = read(p);

  // Block markup is balanced.
  const stack = [];
  for (const m of text.matchAll(/<!--\s*(\/?)wp:([a-z0-9/-]+)[^>]*?(\/?)-->/g)) {
    if (m[3]) continue; // self-closing
    if (!m[1]) stack.push(m[2]);
    else if (stack.pop() !== m[2]) fail(rel(p), `block "${m[2]}" is closed in the wrong place`);
  }
  if (stack.length) fail(rel(p), `block(s) not closed: ${stack.join(', ')}`);

  // Links must work on a sub-site too: use {home}/..., not /....
  if (/href="\/(?!\/)/.test(text)) fail(rel(p), 'root-relative link; write href="{home}/page/" so it works on a sub-site');

  for (const m of text.matchAll(/\[(heal_[a-z_]+)[^\]]*\]/g)) {
    if (!shortcodesDefined.has(m[1])) fail(rel(p), `shortcode [${m[1]}] does not exist`);
  }
}

for (const dir of [pagesDir, dataDir]) {
  const list = dir === pagesDir ? files(dir, '.html') : files(dir, '.json').concat(files(join(dir, 'partners'), '.json').map((f) => `partners/${f}`));
  for (const f of list) {
    const p = join(dir, f);
    const text = read(p);
    for (const r of RULES) {
      const m = r.re.exec(text);
      if (m) (r.error ? fail : warn)(rel(p), `"${m[0]}": ${r.msg}`);
    }
  }
}

// ---------- 3. WordPress files ----------
for (const f of ['style.css', 'functions.php', 'header.php', 'footer.php', 'index.php', 'front-page.php', 'page.php', 'single.php']) {
  if (!existsSync(join(theme, f))) fail(`themes/heal/${f}`, 'missing');
}
if (!/^Theme Name:\s*\S+/m.test(read(join(theme, 'style.css')))) fail('themes/heal/style.css', 'missing "Theme Name:" header');
if (!/^\s*\*\s*Plugin Name:\s*\S+/m.test(read(join(plugin, 'heal-data.php')))) fail('plugins/heal-data/heal-data.php', 'missing "Plugin Name:" header');

for (const f of files(join(plugin, 'includes'), '.php')) {
  if (!/^<\?php/.test(read(join(plugin, 'includes', f)))) fail(`plugins/heal-data/includes/${f}`, 'does not start with <?php');
  if (!read(join(plugin, 'heal-data.php')).includes(`includes/${f}`)) warn(`plugins/heal-data/includes/${f}`, 'is not loaded by heal-data.php');
}

// ---------- Report ----------
for (const w of warnings) console.log(`warning  ${w}`);
for (const e of errors) console.log(`ERROR    ${e}`);
console.log(`\n${errors.length} error(s), ${warnings.length} warning(s)`);
process.exit(errors.length ? 1 : 0);
