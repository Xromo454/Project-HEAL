</main>

<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div>
                <p class="site-footer__brand">HEAL</p>
                <p class="site-footer__lead">A COM-B and Exposome Partnership for Health Engagement.</p>
                <p class="site-footer__small">Health engagement. Everyday cycling. Limassol.</p>
            </div>

            <nav aria-labelledby="footer-explore">
                <p id="footer-explore" class="eyebrow eyebrow--light">Explore HEAL</p>
                <ul class="site-footer__links site-footer__links--2col">
                    <?php
                    $explore = array_merge(heal_nav_items(), [['Participate', home_url('/participate/')]]);
                    foreach ($explore as [$label, $url]) : ?>
                        <li><a href="<?= esc_url($url) ?>"><?= esc_html($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <nav aria-labelledby="footer-info">
                <p id="footer-info" class="eyebrow eyebrow--light">Information</p>
                <ul class="site-footer__links">
                    <?php foreach (heal_footer_info_links() as [$label, $url]) : ?>
                        <li><a href="<?= esc_url($url) ?>"><?= esc_html($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>

        <div class="site-footer__funding">
            <div>
                <p>Co-funded by the European Union · Republic of Cyprus · Research and Innovation Foundation</p>
                <p class="site-footer__small">Project number: <?= esc_html(defined('HEAL_SITE') ? HEAL_SITE['projectNumber'] : 'POST-DOC/0925/ROandOther/0027') ?></p>
            </div>
            <!-- Replace with the official files from RIF. Do not redraw or recolour them. -->
            <div class="funding-placeholder">
                <strong>Official co-funding strip + RESTART 2016–2020 logo</strong>
                <span>Reserved for approved assets · awaiting supply</span>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
