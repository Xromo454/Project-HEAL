<?php
/**
 * "HEAL project start date" field in Settings → General.
 * Replaces PROJECT_START_DATE from site.ts, so the PI can set it without code.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', function () {
    register_setting('general', 'heal_project_start_date', [
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => function ($value) {
            $value = sanitize_text_field((string) $value);
            return DateTimeImmutable::createFromFormat('!Y-m-d', $value) ? $value : '';
        },
    ]);

    add_settings_field(
        'heal_project_start_date',
        'HEAL project start date',
        function () {
            printf(
                '<input type="date" name="heal_project_start_date" value="%s"> <p class="description">%s</p>',
                esc_attr(get_option('heal_project_start_date', '')),
                'Empty = every deliverable shows "Planned".'
            );
        },
        'general'
    );
});

function heal_start_date(): ?string
{
    $date = get_option('heal_project_start_date', '');
    return $date !== '' ? $date : null;
}
