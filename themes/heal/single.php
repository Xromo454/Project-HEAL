<?php
/** A single news post. */
get_header(); ?>

<section class="section">
    <div class="container prose">
        <?php while (have_posts()) : the_post(); ?>
            <article>
                <p class="eyebrow"><a href="<?= esc_url(home_url('/news/')) ?>">News &amp; events</a></p>
                <h1 class="page-title"><?php the_title(); ?></h1>
                <p class="news-meta">
                    <time datetime="<?= esc_attr(get_the_date('c')) ?>"><?= esc_html(get_the_date()) ?></time>
                    <?php foreach (get_the_tags() ?: [] as $tag) : ?>
                        <a href="<?= esc_url(get_tag_link($tag)) ?>">#<?= esc_html($tag->name) ?></a>
                    <?php endforeach; ?>
                </p>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="news-image"><?php the_post_thumbnail('large'); ?></div>
                <?php endif; ?>
                <?php the_content(); ?>
            </article>
            <p><a class="text-link" href="<?= esc_url(home_url('/news/')) ?>">← All news</a></p>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer();
