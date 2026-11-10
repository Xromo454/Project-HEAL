<?php
/**
 * Gallery page (slug "gallery"). Wider than page.php because of the photo grid.
 * The photos are normal Gallery blocks in the page content (Media Library), so the PI edits them in the editor.
 */
get_header(); ?>

<section class="section gallery-intro">
    <div class="container prose prose--wide">
        <?php while (have_posts()) : the_post(); ?>
            <span class="eyebrow">Gallery</span>
            <h1 class="page-title">Places, people and the look of HEAL.</h1>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer();
