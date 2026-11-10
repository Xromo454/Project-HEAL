<?php
/** Gallery page (slug "gallery"). Full-width sections, so it does not use page.php's narrow column. */
get_header(); ?>

<section class="section gallery-intro">
    <div class="container prose">
        <?php while (have_posts()) : the_post(); ?>
            <span class="eyebrow">Gallery</span>
            <h1 class="page-title">Places, people and the look of HEAL.</h1>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</section>

<?php
if (shortcode_exists('heal_gallery')) {
    echo do_shortcode('[heal_gallery]');
}
get_footer();
