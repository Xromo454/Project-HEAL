<?php
/**
 * Contact form: [heal_contact_form]  (US-05)
 *
 * - The message is emailed to the project address (HEAL_SITE['contactEmail']) with wp_mail().
 *   Nothing is saved in the database (NFR-GDPR).
 * - Spam protection without third parties (no CAPTCHA service, no cookies):
 *   a hidden "honeypot" field, a signed form token with a minimum fill time,
 *   and at most HEAL_CONTACT_LIMIT messages per visitor per hour. The limit uses a hash of the
 *   IP address that WordPress deletes after one hour; the IP itself is never stored.
 * - Validation on the server. If something is wrong the form keeps what the visitor typed.
 * - Works without JavaScript. After sending, the page redirects (POST → GET), so reloading
 *   does not send the message twice.
 * - Mail delivery depends on the server. On a server that cannot send mail, use an SMTP plugin
 *   (e.g. WP Mail SMTP) with the CUT mail server; the form then works unchanged.
 */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_CONTACT_LIMIT       = 5;        // messages per visitor per hour
const HEAL_CONTACT_MIN_SECONDS = 3;        // filled in faster than this = probably a bot
const HEAL_CONTACT_MAX_AGE     = 2 * DAY_IN_SECONDS; // form token validity (pages may be cached)
const HEAL_CONTACT_MAX_LENGTH  = ['name' => 100, 'email' => 200, 'message' => 5000];

/** Where messages go. Until the real address is set in config.php, they go to the site admin. */
function heal_contact_recipient(): string
{
    $to = (string) HEAL_SITE['contactEmail'];
    if (!is_email($to) || str_starts_with(strtolower($to), 'todo@')) {
        $to = (string) get_option('admin_email');
    }
    return $to;
}

/** Signed token "time.signature": proves the form came from this site and when it was loaded. */
function heal_contact_token(?int $time = null): string
{
    $time = $time ?? time();
    return $time . '.' . hash_hmac('sha256', 'heal-contact|' . $time, wp_salt('nonce'));
}

/** @return string '' if fine, otherwise an error message */
function heal_contact_check_token(string $token): string
{
    [$time, $sig] = array_pad(explode('.', $token, 2), 2, '');
    $time = (int) $time;
    if ($time <= 0 || !hash_equals(heal_contact_token($time), $token)) {
        return 'The form could not be checked. Please send it again.';
    }
    $age = time() - $time;
    if ($age < HEAL_CONTACT_MIN_SECONDS) {
        return 'That was very fast. Please wait a few seconds and send the form again.';
    }
    if ($age > HEAL_CONTACT_MAX_AGE) {
        return 'This form has expired. Please send it again.';
    }
    return '';
}

/** Key for the hourly limit: a salted hash of the visitor's IP (the IP itself is not stored). */
function heal_contact_rate_key(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    return 'heal_contact_' . substr(hash_hmac('sha256', $ip, wp_salt('auth')), 0, 32);
}

/**
 * Validates and sends one submission.
 * @param array<string,mixed> $post unslashed $_POST
 * @return array{sent:bool, errors:array<string,string>, values:array<string,string>}
 */
function heal_contact_handle(array $post): array
{
    $values = [
        'name'    => trim(sanitize_text_field((string) ($post['heal_name'] ?? ''))),
        'email'   => trim(sanitize_email((string) ($post['heal_email'] ?? ''))),
        'message' => trim(sanitize_textarea_field((string) ($post['heal_message'] ?? ''))),
        'consent' => !empty($post['heal_consent']) ? '1' : '',
    ];
    $result = ['sent' => false, 'errors' => [], 'values' => $values];

    // Honeypot: people never see this field. Pretend it worked so bots learn nothing.
    if (trim((string) ($post['heal_website'] ?? '')) !== '') {
        $result['sent'] = true;
        return $result;
    }

    $errors = [];
    if ($tokenError = heal_contact_check_token((string) ($post['heal_token'] ?? ''))) {
        $errors['form'] = $tokenError;
    }
    if ($values['name'] === '') {
        $errors['name'] = 'Please enter your name.';
    } elseif (mb_strlen($values['name']) > HEAL_CONTACT_MAX_LENGTH['name']) {
        $errors['name'] = 'Please use at most ' . HEAL_CONTACT_MAX_LENGTH['name'] . ' characters.';
    }
    if ($values['email'] === '' || !is_email($values['email'])) {
        $errors['email'] = 'Please enter a valid email address, for example name@example.com.';
    } elseif (mb_strlen($values['email']) > HEAL_CONTACT_MAX_LENGTH['email']) {
        $errors['email'] = 'This email address is too long.';
    }
    if (mb_strlen($values['message']) < 10) {
        $errors['message'] = 'Please write your message (at least 10 characters).';
    } elseif (mb_strlen($values['message']) > HEAL_CONTACT_MAX_LENGTH['message']) {
        $errors['message'] = 'Please shorten your message to at most ' . HEAL_CONTACT_MAX_LENGTH['message'] . ' characters.';
    }
    if ($values['consent'] !== '1') {
        $errors['consent'] = 'Please confirm that we may use your details to reply to you.';
    }

    $key   = heal_contact_rate_key();
    $count = (int) get_transient($key);
    if (!$errors && $count >= HEAL_CONTACT_LIMIT) {
        $errors['form'] = 'You have sent several messages in the last hour. Please try again later or email us directly.';
    }

    if ($errors) {
        $result['errors'] = $errors;
        return $result;
    }

    // Header-safe name (no line breaks or quotes) for Reply-To.
    $safeName = trim(str_replace(["\r", "\n", '"', '<', '>'], ' ', $values['name']));
    $subject  = '[HEAL website] Message from ' . $safeName;
    $body     = "Name: {$values['name']}\n"
        . "Email: {$values['email']}\n"
        . 'Sent: ' . wp_date('j F Y, H:i') . "\n"
        . 'Page: ' . (get_queried_object_id() ? get_permalink(get_queried_object_id()) : home_url('/')) . "\n\n"
        . $values['message'] . "\n\n"
        . "--\nSent from the contact form on " . home_url('/') . ". Reply to this email to answer.\n";
    $headers  = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: "' . $safeName . '" <' . $values['email'] . '>',
    ];

    if (!wp_mail(heal_contact_recipient(), $subject, $body, $headers)) {
        $result['errors']['form'] = 'Sorry, your message could not be sent. Please email us directly at '
            . HEAL_SITE['contactEmail'] . '.';
        return $result;
    }

    set_transient($key, $count + 1, HOUR_IN_SECONDS);
    $result['sent'] = true;
    return $result;
}

/** Handle the POST before the page is rendered, then redirect after success (no double sending). */
add_action('template_redirect', function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['heal_contact'])) {
        return;
    }
    $result = heal_contact_handle(wp_unslash($_POST));
    if ($result['sent']) {
        $page = get_queried_object_id() ? get_permalink(get_queried_object_id()) : home_url('/');
        wp_safe_redirect(add_query_arg('sent', '1', $page) . '#contact-form', 303);
        exit;
    }
    $GLOBALS['heal_contact_result'] = $result;
});

add_shortcode('heal_contact_form', function () {
    $result = $GLOBALS['heal_contact_result'] ?? ['errors' => [], 'values' => []];
    $errors = $result['errors'];
    $v      = $result['values'] + ['name' => '', 'email' => '', 'message' => '', 'consent' => ''];
    $field  = fn(string $id): string => $errors[$id] ?? '';
    $labels = ['name' => 'Name', 'email' => 'Email', 'message' => 'Message', 'consent' => 'Consent'];

    ob_start(); ?>
    <div class="heal-contact" id="contact-form">
    <?php if (isset($_GET['sent']) && !$errors) : ?>
        <div class="heal-contact__success" role="status">
            <h2>Thank you, your message has been sent.</h2>
            <p>We will reply to your email address as soon as we can.</p>
            <p><a href="<?= esc_url(remove_query_arg('sent')) ?>#contact-form">Send another message</a></p>
        </div>
    <?php else : ?>
        <?php if ($errors) : ?>
            <div class="heal-contact__errors" role="alert" tabindex="-1">
                <h2>Please check the form</h2>
                <ul>
                <?php foreach ($errors as $id => $message) : ?>
                    <li><?php if ($id === 'form') : ?><?= esc_html($message) ?><?php else : ?><a href="#heal-<?= esc_attr($id) ?>"><?= esc_html($labels[$id] . ': ' . $message) ?></a><?php endif; ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="heal-contact__form" method="post" action="<?= esc_url(remove_query_arg('sent')) ?>#contact-form" novalidate>
            <input type="hidden" name="heal_contact" value="1">
            <input type="hidden" name="heal_token" value="<?= esc_attr(heal_contact_token()) ?>">

            <div class="heal-field<?= $field('name') ? ' heal-field--error' : '' ?>">
                <label for="heal-name">Name</label>
                <?php if ($field('name')) : ?><p class="heal-field__error" id="heal-name-error"><?= esc_html($field('name')) ?></p><?php endif; ?>
                <input type="text" id="heal-name" name="heal_name" value="<?= esc_attr($v['name']) ?>"
                       autocomplete="name" required maxlength="<?= HEAL_CONTACT_MAX_LENGTH['name'] ?>"
                       <?= $field('name') ? 'aria-invalid="true" aria-describedby="heal-name-error"' : '' ?>>
            </div>

            <div class="heal-field<?= $field('email') ? ' heal-field--error' : '' ?>">
                <label for="heal-email">Email</label>
                <?php if ($field('email')) : ?><p class="heal-field__error" id="heal-email-error"><?= esc_html($field('email')) ?></p><?php endif; ?>
                <input type="email" id="heal-email" name="heal_email" value="<?= esc_attr($v['email']) ?>"
                       autocomplete="email" required maxlength="<?= HEAL_CONTACT_MAX_LENGTH['email'] ?>"
                       <?= $field('email') ? 'aria-invalid="true" aria-describedby="heal-email-error"' : '' ?>>
            </div>

            <div class="heal-field<?= $field('message') ? ' heal-field--error' : '' ?>">
                <label for="heal-message">Message</label>
                <p class="heal-field__hint" id="heal-message-hint">Please do not include health information.</p>
                <?php if ($field('message')) : ?><p class="heal-field__error" id="heal-message-error"><?= esc_html($field('message')) ?></p><?php endif; ?>
                <textarea id="heal-message" name="heal_message" rows="7" required
                          maxlength="<?= HEAL_CONTACT_MAX_LENGTH['message'] ?>"
                          aria-describedby="heal-message-hint<?= $field('message') ? ' heal-message-error' : '' ?>"
                          <?= $field('message') ? 'aria-invalid="true"' : '' ?>><?= esc_textarea($v['message']) ?></textarea>
            </div>

            <!-- Honeypot: hidden from people and screen readers; bots fill it in. -->
            <div class="heal-field heal-field--hp" aria-hidden="true">
                <label for="heal-website">Website (leave empty)</label>
                <input type="text" id="heal-website" name="heal_website" value="" tabindex="-1" autocomplete="off">
            </div>

            <div class="heal-field heal-field--check<?= $field('consent') ? ' heal-field--error' : '' ?>">
                <?php if ($field('consent')) : ?><p class="heal-field__error" id="heal-consent-error"><?= esc_html($field('consent')) ?></p><?php endif; ?>
                <input type="checkbox" id="heal-consent" name="heal_consent" value="1" required
                       <?= $v['consent'] === '1' ? 'checked' : '' ?>
                       <?= $field('consent') ? 'aria-invalid="true" aria-describedby="heal-consent-error"' : '' ?>>
                <label for="heal-consent">I agree that the HEAL project team uses my name, email address and message to reply to me.</label>
            </div>

            <p class="heal-contact__notice">
                Your message is sent by email to the project team at the Cyprus University of Technology.
                It is not stored on this website. See our <a href="<?= esc_url(home_url('/privacy/')) ?>">privacy policy</a>.
            </p>

            <button type="submit" class="btn btn--solid">Send message</button>
        </form>
    <?php endif; ?>
    </div>
    <?php return ob_get_clean();
});
