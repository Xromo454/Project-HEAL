<?php
/**
 * Hardening: stops the site giving away information it does not need to give.
 *
 *  - Usernames: the public list of users (REST and ?author=1), author pages, the users sitemap,
 *    the author in the RSS feed and in oEmbed previews, and the "unknown username" login
 *    message are closed. Author pages lead to the home page for everyone, also for logged-in users.
 *  - Version: the WordPress version is removed from the page head and from core file links.
 *  - Comments and pingbacks are off (the site collects no visitor data), and XML-RPC is off.
 *  - Basic security headers.
 *
 * Not done here, because it is the server's job (CUT IT): HTTPS and HSTS, Content-Security-Policy,
 * hiding readme.html and license.txt in the WordPress folder.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ---------- Usernames ---------- */

add_filter('rest_endpoints', function (array $endpoints): array {
    if (is_user_logged_in()) {
        return $endpoints;
    }
    foreach (array_keys($endpoints) as $route) {
        if (str_starts_with($route, '/wp/v2/users')) {
            unset($endpoints[$route]);
        }
    }
    return $endpoints;
});

// /?author=1 and /author/admin/ would show the username. There is one team account and no author pages,
// so they lead to the home page for everyone (also for logged-in users).
add_action('template_redirect', function () {
    if (is_author() || isset($_GET['author'])) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}, 1);

add_filter('wp_sitemaps_add_provider', fn($provider, $name) => $name === 'users' ? false : $provider, 10, 2);

// RSS feed (<dc:creator>) and oEmbed link previews (author_name, author_url → /author/<username>/)
// would still show the author. Show the site name instead and drop the author link.
add_filter('the_author', fn($name) => is_feed() ? get_bloginfo('name') : $name);
add_filter('oembed_response_data', function (array $data): array {
    $data['author_name'] = get_bloginfo('name');
    $data['author_url']  = home_url('/');
    return $data;
});

// The same message whether the username or the password is wrong.
add_filter('login_errors', fn() => 'The username or password is not correct.');

/* ---------- Version ---------- */

remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

$heal_strip_version = function (string $src): string {
    return str_contains($src, 'ver=' . get_bloginfo('version')) ? remove_query_arg('ver', $src) : $src;
};
add_filter('style_loader_src', $heal_strip_version, 9999);
add_filter('script_loader_src', $heal_strip_version, 9999);

/* ---------- No comments, no pingbacks, no XML-RPC ---------- */

add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', '__return_empty_array'); // also the methods that need no login (list, pingback)
add_filter('comments_open', '__return_false', 20);
add_filter('pings_open', '__return_false', 20);
add_filter('comments_array', '__return_empty_array', 20);
add_action('init', function () {
    foreach (['post', 'page'] as $type) {
        remove_post_type_support($type, 'comments');
        remove_post_type_support($type, 'trackbacks');
    }
}, 100);

/* ---------- Headers ---------- */

add_action('send_headers', function () {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
});
