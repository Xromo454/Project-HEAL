<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
    <div class="container site-header__top">
        <a class="brand" href="<?= esc_url(home_url('/')) ?>" aria-label="HEAL home">
            <img class="brand__logo" src="<?= esc_url(get_theme_file_uri('assets/logo.png')) ?>" alt="HEAL" width="686" height="316">
            <span class="brand__tagline">A COM-B and Exposome Partnership<br>for Health Engagement</span>
        </a>

        <div class="site-header__tools">
            <nav class="lang" aria-label="Language">
                <a href="<?= esc_url(home_url('/')) ?>" lang="en" aria-current="true">EN</a>
                <span class="lang__soon" lang="el" title="Greek version coming soon">EL</span>
            </nav>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav">Menu</button>
        </div>
    </div>

    <div class="site-header__nav">
        <div class="container">
            <nav id="site-nav" class="main-nav" aria-label="Main">
                <ul>
                    <?php foreach (heal_nav_items() as [$label, $url]) : ?>
                        <li><a href="<?= esc_url($url) ?>" <?= heal_is_current($url) ? 'aria-current="page"' : '' ?>><?= esc_html($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <a class="btn btn--outline main-nav__cta" href="<?= esc_url(home_url('/participate/')) ?>">Participate <?= heal_arrow() ?></a>
            </nav>
        </div>
    </div>
</header>

<main id="main">
