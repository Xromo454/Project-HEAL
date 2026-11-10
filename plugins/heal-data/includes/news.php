<?php
/**
 * News: normal WordPress posts, listed on the News page by [heal_news].
 *
 * The PI writes news under Posts → Add New: title, text, featured image (used for link previews),
 * tags. Each post gets its own page (themes/heal/single.php). RSS is at /feed/.
 *
 *   [heal_news]              the 6 latest posts, newest first, with "Newer / Older" links
 *   [heal_news per_page="3"] a different number per page
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('heal_news', function ($atts) {
    $atts    = shortcode_atts(['per_page' => 6], $atts, 'heal_news');
    $perPage = max(1, min(20, (int) $atts['per_page']));
    $paged   = isset($_GET['news_page']) ? max(1, absint($_GET['news_page'])) : 1;
    $pageUrl = get_permalink(get_queried_object_id()); // before the loop changes the current post

    $q = new WP_Query([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $perPage,
        'paged'               => $paged,
        'ignore_sticky_posts' => true,
    ]);

    if (!$q->have_posts()) {
        return '<p>No news yet. Approved project updates will appear here.</p>';
    }

    ob_start(); ?>
    <div class="news-list">
    <?php while ($q->have_posts()) : $q->the_post(); ?>
        <article class="news-item">
            <time class="news-item__date" datetime="<?= esc_attr(get_the_date('c')) ?>"><?= esc_html(get_the_date()) ?></time>
            <h3 class="news-item__title"><a href="<?= esc_url(get_permalink()) ?>"><?= esc_html(get_the_title()) ?></a></h3>
            <p class="news-item__summary"><?= esc_html(wp_trim_words(get_the_excerpt(), 35)) ?></p>
            <?php $tags = get_the_tags();
            if ($tags) : ?>
                <p class="news-item__tags">
                <?php foreach ($tags as $tag) : ?>
                    <a href="<?= esc_url(get_tag_link($tag)) ?>">#<?= esc_html($tag->name) ?></a>
                <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
    </div>

    <?php
    if ($q->max_num_pages > 1) {
        $base = str_replace(999999999, '%#%', add_query_arg('news_page', 999999999, $pageUrl));
        echo '<nav class="news-pagination" aria-label="News pages">' . paginate_links([
            'base'      => $base,
            'format'    => '',
            'current'   => $paged,
            'total'     => $q->max_num_pages,
            'prev_text' => '← Newer',
            'next_text' => 'Older →',
            'type'      => 'list',
        ]) . '</nav>';
    }
    wp_reset_postdata();

    return ob_get_clean();
});
