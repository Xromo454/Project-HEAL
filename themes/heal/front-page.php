<?php
/**
 * Home page. Layout and texts from the Figma prototype (prototype 1-1-2).
 */
get_header();

$deliverables = heal_has_data() ? heal_public_deliverables() : [];
$wpCount = heal_has_data() ? count(heal_work_packages()) : 5;
$partners = heal_has_data() ? heal_partners() : [];
$currentMonth = heal_has_data() ? heal_project_month(heal_start_date()) : null; // for the deliverable status
?>

<!-- Hero -->
<section class="hero">
    <div class="container hero__grid">
        <div>
            <p class="eyebrow eyebrow--navy">Limassol · Public-health research</p>
            <h1 class="hero__title">Everyday cycling.<br>Shared possibilities.</h1>
            <p class="hero__lead">HEAL aims to explore how behavioural science and environmental context can support health engagement through everyday cycling in Limassol.</p>
            <a class="btn btn--solid" href="<?= esc_url(home_url('/project/')) ?>">Explore the project</a>
            <p><a class="text-link" href="<?= esc_url(home_url('/participate/')) ?>">About participation <?= heal_arrow() ?></a></p>
            <p class="muted">A COM-B and Exposome Partnership for Health Engagement.</p>
        </div>

        <aside class="approach-card" aria-label="The HEAL approach">
            <div class="approach-card__head">
                <p class="eyebrow">The HEAL approach</p>
                <span class="approach-card__loop" aria-hidden="true">↻</span>
            </div>
            <div class="approach-card__pair">
                <div class="tile tile--orange"><strong>COM-B</strong><span>The person</span></div>
                <div class="tile tile--orange"><strong>Exposome</strong><span>The context</span></div>
            </div>
            <p class="approach-card__arrow" aria-hidden="true">↓</p>
            <div class="tile tile--navy">
                <span class="eyebrow eyebrow--light">Just-in-time support</span>
                <strong class="tile__big">A timely nudge.<br>A reason to ride.</strong>
            </div>
            <div class="approach-card__foot">
                <span class="approach-card__loop" aria-hidden="true">↻</span>
                <div><strong>Verified NextBike ride</strong><span>Ride data feeds back into the loop.</span></div>
            </div>
        </aside>
    </div>
</section>

<!-- Key figures -->
<section class="container stats" aria-label="Key figures">
    <dl class="stats__grid">
        <div><dt>months of research</dt><dd>24</dd></div>
        <div><dt>adults in the pilot</dt><dd>20</dd></div>
        <div><dt>month intervention</dt><dd>6</dd></div>
        <div><dt>work packages</dt><dd><?= (int) $wpCount ?></dd></div>
    </dl>
</section>

<!-- 01 Approach -->
<section class="section">
    <div class="container">
        <p class="eyebrow">01 / The approach</p>
        <h2 class="section__title">People and place,<br>connected by research.</h2>
        <p class="section__intro">What makes a journey possible? HEAL brings together behavioural factors, environmental context and timely support in a closed feedback loop.</p>

        <ol class="steps">
            <li>
                <span class="steps__num">01 —</span>
                <h3>Understand the person</h3>
                <p>COM-B considers Capability, Opportunity and Motivation in relation to Behaviour.</p>
            </li>
            <li>
                <span class="steps__num">02 —</span>
                <h3>Consider the surroundings</h3>
                <p>The exposome brings environmental context into the question of when to offer support.</p>
            </li>
            <li>
                <span class="steps__num">03 —</span>
                <h3>Support, ride, learn</h3>
                <p>A just-in-time adaptive nudge and micro-incentive aim to support engagement. A verified ride feeds back as data.</p>
            </li>
        </ol>
        <a class="text-link" href="<?= esc_url(home_url('/project/how-it-works/')) ?>">How the HEAL approach works <?= heal_arrow() ?></a>
    </div>
</section>

<!-- 02 Living lab -->
<section class="section section--alt">
    <div class="container split">
        <div>
            <p class="eyebrow">02 / Limassol living lab</p>
            <h2 class="section__title">A city setting.<br>An everyday question.</h2>
            <p>Limassol is among the 100 EU Mission cities working towards climate neutrality by 2030. HEAL places health engagement and everyday cycling within this local context.</p>
            <a class="text-link" href="<?= esc_url(home_url('/project/living-lab/')) ?>">Explore the Limassol living lab <?= heal_arrow() ?></a>
        </div>
        <div class="panel">
            <p class="eyebrow eyebrow--navy">Place / People / Practice</p>
            <dl class="panel__list">
                <div><dt>Place</dt><dd>Limassol, an EU Mission city</dd></div>
                <div><dt>People</dt><dd>Limassol citizens taking part in the pilot</dd></div>
                <div><dt>Practice</dt><dd>Everyday cycling with NextBike</dd></div>
            </dl>
        </div>
    </div>
</section>

<!-- 03 Research programme -->
<section class="section">
    <div class="container split">
        <div>
            <p class="eyebrow">03 / The research programme</p>
            <h2 class="section__title">A small pilot.<br>A structured plan.</h2>
            <p>The 24-month project includes a feasibility pilot randomised controlled trial with 20 adults and 1:1 allocation. The planned six-month intervention runs from M10 to M15.</p>
            <a class="text-link" href="<?= esc_url(home_url('/work-plan/')) ?>">See the work plan <?= heal_arrow() ?></a>
        </div>
        <div>
            <dl class="timeline">
                <div><dt>M1–M24</dt><dd>Five coordinated work packages</dd></div>
                <div><dt>M7–M10</dt><dd>Planned recruitment stage</dd></div>
                <div><dt>M10–M15</dt><dd>Six-month intervention</dd></div>
                <div><dt>M24</dt><dd>Target: TRL5</dd></div>
            </dl>
            <p class="small">M = project month. The project start date is not fixed. Research aims, not findings, are presented here.</p>
        </div>
    </div>
</section>

<!-- 04 Open research -->
<section class="section">
    <div class="container">
        <p class="eyebrow">04 / Open research</p>
        <h2 class="section__title">Planned to be shared.</h2>
        <p>Public outputs will be listed as they are approved for release. These are planned deliverables, not available downloads.</p>

        <ul class="cards cards--3">
            <?php foreach (array_slice($deliverables, 0, 3) as $d) :
                $status = heal_deliverable_status($d, $currentMonth); // same status as on the Results page ?>
                <li class="card">
                    <span class="card__meta"><?= esc_html($d['id']) ?> · Due M<?= (int) $d['dueMonth'] ?></span>
                    <h3><?= esc_html($d['title']) ?></h3>
                    <span class="badge"><?= esc_html(HEAL_STATUS_LABEL[$status]) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <a class="text-link" href="<?= esc_url(home_url('/results/')) ?>">View all <?= count($deliverables) ?: 'five' ?> planned public deliverables <?= heal_arrow() ?></a>
    </div>
</section>

<!-- 05 News & events -->
<section class="section">
    <div class="container">
        <p class="eyebrow">05 / News &amp; events</p>
        <h2 class="section__title">Updates as the work unfolds.</h2>
        <div class="cards cards--2">
            <a class="card card--link" href="<?= esc_url(home_url('/news/')) ?>">
                <h3>Project news</h3>
                <p>Approved project updates will appear here.</p>
            </a>
            <a class="card card--link" href="<?= esc_url(home_url('/news/')) ?>">
                <h3>Events &amp; consultation</h3>
                <p>Details of public events will be shared after approval.</p>
            </a>
        </div>
    </div>
</section>

<!-- 06 People -->
<section class="section section--alt">
    <div class="container">
        <p class="eyebrow">06 / The people behind the project</p>
        <h2 class="section__title">Research and mobility,<br>in partnership.</h2>

        <div class="partners">
            <?php
            $roleLabel = ['Host Organisation' => 'Host organisation', 'Partner' => 'Partner · PA1', 'End user / stakeholder' => 'End user / stakeholder'];
            foreach ($partners as $key => $p) : ?>
                <article class="partner">
                    <p class="eyebrow"><?= esc_html($roleLabel[$p['role']] ?? $p['role']) ?></p>
                    <h3><?= esc_html($p['name']) ?></h3>
                    <?php if ($key === 'cut') : ?>
                        <p>CUT · CII/PEHL</p>
                        <p class="small">PI / Product Owner: Dr Antonio Bovolini</p>
                    <?php else : ?>
                        <p><?= esc_html($p['description']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <a class="text-link" href="<?= esc_url(home_url('/consortium/')) ?>">Consortium &amp; team <?= heal_arrow() ?></a>
    </div>
</section>

<!-- 07 Participation -->
<section class="section section--alt section--last">
    <div class="container split split--cta">
        <div>
            <p class="eyebrow">07 / Participation information</p>
            <h2 class="section__title">Curious about taking part?</h2>
            <p>Start with the planned study and what is still awaiting approval. This website is currently information only; recruitment is not open.</p>
        </div>
        <div class="stack">
            <a class="btn btn--solid" href="<?= esc_url(home_url('/participate/')) ?>">Participation information</a>
            <a class="btn btn--navy-outline" href="<?= esc_url(home_url('/contact/')) ?>">Contact the team</a>
        </div>
    </div>
</section>

<?php get_footer();
