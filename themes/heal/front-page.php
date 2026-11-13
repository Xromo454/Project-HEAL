<?php
/**
 * Home page. Layout and texts from the Figma prototype (prototype 1-1-2).
 * Texts are translated (text domain "heal"); links go to the pages in the current language.
 */
get_header();

$deliverables = heal_has_data() ? heal_public_deliverables() : [];
$wpCount = heal_has_data() ? count(heal_work_packages()) : 5;
$partners = heal_has_data() ? heal_partners() : [];
$currentMonth = heal_has_data() ? heal_project_month(heal_start_date()) : null; // for the deliverable status
$br = ['br' => []]; // texts that keep a line break
?>

<!-- Hero (the decorative route above it comes from header.php) -->
<section class="hero">
    <div class="container hero__grid">
        <div>
            <p class="eyebrow eyebrow--navy"><?php esc_html_e('Limassol · Public-health research', 'heal'); ?></p>
            <h1 class="hero__title"><?php esc_html_e('Where daily cycling meets a healthier future.', 'heal'); ?></h1>
            <p class="hero__lead"><?php esc_html_e('HEAL aims to explore how behavioural science and environmental context can support health engagement through everyday cycling in Limassol.', 'heal'); ?></p>
            <a class="btn btn--solid" href="<?= esc_url(heal_url('/project/')) ?>"><?php esc_html_e('Explore the project', 'heal'); ?></a>
            <p><a class="text-link" href="<?= esc_url(heal_url('/participate/')) ?>"><?php esc_html_e('About participation', 'heal'); ?> <?= heal_arrow() ?></a></p>
            <p class="muted"><?php esc_html_e('A COM-B and Exposome Partnership for Health Engagement.', 'heal'); ?></p>
        </div>

        <aside class="approach-card" aria-label="<?php esc_attr_e('The HEAL approach', 'heal'); ?>">
            <div class="approach-card__head">
                <p class="eyebrow"><?php esc_html_e('The HEAL approach', 'heal'); ?></p>
                <span class="approach-card__loop" aria-hidden="true">↻</span>
            </div>
            <div class="approach-card__pair">
                <div class="tile tile--orange"><strong>COM-B</strong><span><?php esc_html_e('The person', 'heal'); ?></span></div>
                <div class="tile tile--orange"><strong><?php esc_html_e('Exposome', 'heal'); ?></strong><span><?php esc_html_e('The context', 'heal'); ?></span></div>
            </div>
            <p class="approach-card__arrow" aria-hidden="true">↓</p>
            <div class="tile tile--navy">
                <span class="eyebrow eyebrow--light"><?php esc_html_e('Just-in-time support', 'heal'); ?></span>
                <strong class="tile__big"><?= wp_kses(__('A timely nudge.<br>A reason to ride.', 'heal'), $br) ?></strong>
            </div>
            <div class="approach-card__foot">
                <span class="approach-card__loop" aria-hidden="true">↻</span>
                <div><strong><?php esc_html_e('Verified NextBike ride', 'heal'); ?></strong><span><?php esc_html_e('Ride data feeds back into the loop.', 'heal'); ?></span></div>
            </div>
        </aside>
    </div>
</section>

<!-- Key figures -->
<section class="container stats" aria-label="<?php esc_attr_e('Key figures', 'heal'); ?>">
    <dl class="stats__grid">
        <div><dt><?php esc_html_e('months of research', 'heal'); ?></dt><dd>24</dd></div>
        <div><dt><?php esc_html_e('adults in the pilot', 'heal'); ?></dt><dd>20</dd></div>
        <div><dt><?php esc_html_e('month intervention', 'heal'); ?></dt><dd>6</dd></div>
        <div><dt><?php esc_html_e('work packages', 'heal'); ?></dt><dd><?= (int) $wpCount ?></dd></div>
    </dl>
</section>

<!-- 01 Approach -->
<section class="section">
    <div class="container">
        <p class="eyebrow"><?php esc_html_e('01 / The approach', 'heal'); ?></p>
        <h2 class="section__title"><?= wp_kses(__('People and place,<br>connected by research.', 'heal'), $br) ?></h2>
        <p class="section__intro"><?php esc_html_e('What makes a journey possible? HEAL brings together behavioural factors, environmental context and timely support in a closed feedback loop.', 'heal'); ?></p>

        <ol class="steps">
            <li>
                <span class="steps__num">01 —</span>
                <h3><?php esc_html_e('Understand the person', 'heal'); ?></h3>
                <p><?php esc_html_e('COM-B considers Capability, Opportunity and Motivation in relation to Behaviour.', 'heal'); ?></p>
            </li>
            <li>
                <span class="steps__num">02 —</span>
                <h3><?php esc_html_e('Consider the surroundings', 'heal'); ?></h3>
                <p><?php esc_html_e('The exposome brings environmental context into the question of when to offer support.', 'heal'); ?></p>
            </li>
            <li>
                <span class="steps__num">03 —</span>
                <h3><?php esc_html_e('Support, ride, learn', 'heal'); ?></h3>
                <p><?php esc_html_e('A just-in-time adaptive nudge and micro-incentive aim to support engagement. A verified ride feeds back as data.', 'heal'); ?></p>
            </li>
        </ol>
        <a class="text-link" href="<?= esc_url(heal_url('/project/how-it-works/')) ?>"><?php esc_html_e('How the HEAL approach works', 'heal'); ?> <?= heal_arrow() ?></a>
    </div>
</section>

<!-- 02 Living lab -->
<section class="section section--alt">
    <div class="container split">
        <div>
            <p class="eyebrow"><?php esc_html_e('02 / Limassol living lab', 'heal'); ?></p>
            <h2 class="section__title"><?= wp_kses(__('A city setting.<br>An everyday question.', 'heal'), $br) ?></h2>
            <p><?php esc_html_e('Limassol is among the 100 EU Mission cities working towards climate neutrality by 2030. HEAL places health engagement and everyday cycling within this local context.', 'heal'); ?></p>
            <a class="text-link" href="<?= esc_url(heal_url('/project/living-lab/')) ?>"><?php esc_html_e('Explore the Limassol living lab', 'heal'); ?> <?= heal_arrow() ?></a>
        </div>
        <div class="panel">
            <p class="eyebrow eyebrow--navy"><?php esc_html_e('Place / People / Practice', 'heal'); ?></p>
            <dl class="panel__list">
                <div><dt><?php esc_html_e('Place', 'heal'); ?></dt><dd><?php esc_html_e('Limassol, an EU Mission city', 'heal'); ?></dd></div>
                <div><dt><?php esc_html_e('People', 'heal'); ?></dt><dd><?php esc_html_e('Limassol citizens taking part in the pilot', 'heal'); ?></dd></div>
                <div><dt><?php esc_html_e('Practice', 'heal'); ?></dt><dd><?php esc_html_e('Everyday cycling with NextBike', 'heal'); ?></dd></div>
            </dl>
        </div>
    </div>
</section>

<!-- 03 Research programme -->
<section class="section">
    <div class="container split">
        <div>
            <p class="eyebrow"><?php esc_html_e('03 / The research programme', 'heal'); ?></p>
            <h2 class="section__title"><?= wp_kses(__('A small pilot.<br>A structured plan.', 'heal'), $br) ?></h2>
            <p><?php esc_html_e('The 24-month project includes a feasibility pilot randomised controlled trial with 20 adults and 1:1 allocation. The planned six-month intervention runs from M10 to M15.', 'heal'); ?></p>
            <a class="text-link" href="<?= esc_url(heal_url('/work-plan/')) ?>"><?php esc_html_e('See the work plan', 'heal'); ?> <?= heal_arrow() ?></a>
        </div>
        <div>
            <dl class="timeline">
                <div><dt>M1–M24</dt><dd><?php esc_html_e('Five coordinated work packages', 'heal'); ?></dd></div>
                <div><dt>M7–M10</dt><dd><?php esc_html_e('Planned recruitment stage', 'heal'); ?></dd></div>
                <div><dt>M10–M15</dt><dd><?php esc_html_e('Six-month intervention', 'heal'); ?></dd></div>
                <div><dt>M24</dt><dd><?php esc_html_e('Target: TRL5', 'heal'); ?></dd></div>
            </dl>
            <p class="small"><?php esc_html_e('M = project month. The project start date is not fixed. Research aims, not findings, are presented here.', 'heal'); ?></p>
        </div>
    </div>
</section>

<!-- 04 Open research -->
<section class="section">
    <div class="container">
        <p class="eyebrow"><?php esc_html_e('04 / Open research', 'heal'); ?></p>
        <h2 class="section__title"><?php esc_html_e('Planned to be shared.', 'heal'); ?></h2>
        <p><?php esc_html_e('Public outputs will be listed as they are approved for release. These are planned deliverables, not available downloads.', 'heal'); ?></p>

        <ul class="cards cards--3">
            <?php foreach (array_slice($deliverables, 0, 3) as $d) :
                $status = heal_deliverable_status($d, $currentMonth); // same status as on the Results page ?>
                <li class="card">
                    <span class="card__meta"><?= esc_html($d['id']) ?> · <?= esc_html(sprintf(/* translators: %d: project month */ __('Due M%d', 'heal'), (int) $d['dueMonth'])) ?></span>
                    <h3><?= esc_html($d['title']) ?></h3>
                    <span class="badge"><?= esc_html(heal_status_label($status)) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <a class="text-link" href="<?= esc_url(heal_url('/results/')) ?>"><?= esc_html(sprintf(
            /* translators: %d: number of public deliverables */
            _n('View the %d planned public deliverable', 'View all %d planned public deliverables', count($deliverables) ?: 5, 'heal'),
            count($deliverables) ?: 5
        )) ?> <?= heal_arrow() ?></a>
    </div>
</section>

<!-- 05 News & events -->
<section class="section">
    <div class="container">
        <p class="eyebrow"><?php esc_html_e('05 / News & events', 'heal'); ?></p>
        <h2 class="section__title"><?php esc_html_e('Updates as the work unfolds.', 'heal'); ?></h2>
        <div class="cards cards--2">
            <a class="card card--link" href="<?= esc_url(heal_url('/news/')) ?>">
                <h3><?php esc_html_e('Project news', 'heal'); ?></h3>
                <p><?php esc_html_e('Approved project updates will appear here.', 'heal'); ?></p>
            </a>
            <a class="card card--link" href="<?= esc_url(heal_url('/news/')) ?>">
                <h3><?php esc_html_e('Events & consultation', 'heal'); ?></h3>
                <p><?php esc_html_e('Details of public events will be shared after approval.', 'heal'); ?></p>
            </a>
        </div>
    </div>
</section>

<!-- 06 People -->
<section class="section section--alt">
    <div class="container">
        <p class="eyebrow"><?php esc_html_e('06 / The people behind the project', 'heal'); ?></p>
        <h2 class="section__title"><?= wp_kses(__('Research and mobility,<br>in partnership.', 'heal'), $br) ?></h2>

        <div class="partners">
            <?php
            $roleLabel = [
                'Host Organisation'      => __('Host organisation', 'heal'),
                'Partner'                => __('Partner · PA1', 'heal'),
                'End user / stakeholder' => __('End user / stakeholder', 'heal'),
            ];
            foreach ($partners as $key => $p) : ?>
                <article class="partner">
                    <p class="eyebrow"><?= esc_html($roleLabel[$p['role']] ?? $p['role']) ?></p>
                    <h3><?= esc_html($p['name']) ?></h3>
                    <?php if ($key === 'cut') : ?>
                        <p>CUT · CII/PEHL</p>
                        <p class="small"><?php esc_html_e('PI / Product Owner: Dr Antonio Bovolini', 'heal'); ?></p>
                    <?php else : ?>
                        <p><?= esc_html($p['description']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <a class="text-link" href="<?= esc_url(heal_url('/consortium/')) ?>"><?php esc_html_e('Consortium & team', 'heal'); ?> <?= heal_arrow() ?></a>
    </div>
</section>

<!-- 07 Participation -->
<section class="section section--alt section--last">
    <div class="container split split--cta">
        <div>
            <p class="eyebrow"><?php esc_html_e('07 / Participation information', 'heal'); ?></p>
            <h2 class="section__title"><?php esc_html_e('Curious about taking part?', 'heal'); ?></h2>
            <p><?php esc_html_e('Start with the planned study and what is still awaiting approval. This website is currently information only; recruitment is not open.', 'heal'); ?></p>
        </div>
        <div class="stack">
            <a class="btn btn--solid" href="<?= esc_url(heal_url('/participate/')) ?>"><?php esc_html_e('Participation information', 'heal'); ?></a>
            <a class="btn btn--navy-outline" href="<?= esc_url(heal_url('/contact/')) ?>"><?php esc_html_e('Contact the team', 'heal'); ?></a>
        </div>
    </div>
</section>

<?php get_footer();
