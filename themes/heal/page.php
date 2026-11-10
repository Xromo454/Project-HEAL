<?php
/** Any normal page (Results, Work plan, …). Content comes from the editor, incl. [heal_*] shortcodes. */
get_header(); ?>

<section class="section">
    <div class="container prose">
        <?php while (have_posts()) : the_post(); ?>
            <h1 class="page-title"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer();
