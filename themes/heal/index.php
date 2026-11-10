<?php
/** Fallback template (blog list, archives, 404). */
get_header(); ?>

<section class="section">
    <div class="container prose">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article class="post-item">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php the_excerpt(); ?>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <h1 class="page-title">Page not found</h1>
            <p>This page does not exist yet. <a class="text-link" href="<?= esc_url(home_url('/')) ?>">Back to home</a></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer();
