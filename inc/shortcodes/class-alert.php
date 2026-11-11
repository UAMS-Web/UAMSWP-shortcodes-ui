<?php

declare(strict_types=1);

/**
 * Alerts Shortcode
 *
 * Generates a bootstrap-styled Alert
 * with lots of variety
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Alert
 */
class Alert extends Shortcode
{
    /**
     * declare default attributes
     */
    private static array $defaults = [
        'type' => 'info',
        'dismiss' => false,
        'customclass' => null,
    ];

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('Alert', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-info',
            'inner_content' => [
                'label' => esc_html__('Alert Body', 'uams_shortcodes'),
                'description' => esc_html__('Main content of your alert. Text and basic HTML tags can be added here.', 'uams_shortcodes'),
                'encode' => true,
            ],
            'attrs' => self::get_attributes(),
        ];
    }

    /**
     * provide UI items for shortcake
     */
    public static function get_attributes(): array
    {
        return [
            [
                'label' => esc_html__('Alert Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => 'See examples of <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/alerts/" target="_blank">each type of alert</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => [
                    'success' => 'Success',
                    'info' => 'Information',
                    'help' => 'Help',
                    'warning' => 'Warning',
                    'danger' => 'Danger',
                ],
            ],
            [
                'label' => esc_html__('Dismissable?', 'uams_shortcodes'),
                'attr' => 'dismiss',
                'type' => 'checkbox',
                'description' => '',
            ],
            [
                'label' => esc_html__('Custom CSS classes', 'uams_shortcodes'),
                'attr' => 'customclass',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Comma-separated list',
                ],
            ],
        ];
    }

    /**
     * return (not echo) our Alert element
     */
    public static function callback($attrs, $content = ''): string
    {
        $attrs = array_merge(self::$defaults, $attrs);

        // Valid types only
        if (! empty($attrs['type'])) {
            $type = (in_array($attrs['type'], ['success', 'help', 'warning', 'danger'])) ? sanitize_html_class($attrs['type']) : 'info';
        } else {
            $type = 'info';
        }

        $icon = match ($type) {
            'success' => 'glyphicon-checkbox',
            'info' => 'dashicons-info',
            'help' => 'glyphicon-question',
            'warning' => 'dashicons-warning',
            'danger' => 'glyphicon-x',
            default => 'dashicons-info',
        };

        if ($attrs['dismiss'] == true) {
            $dismiss = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        } else {
            $dismiss = null;
        }

        if (! empty($icon)) {
            // @pest-mutate-ignore: IncrementInteger
            $icon_type = explode('-', $icon, 2)[0];
            // @pest-mutate-ignore: IncrementInteger
            $icon_name = explode('-', $icon, 2)[1];

            $icon_display = '<span aria-hidden="true">'.self::uams_shortcodes_get_contents(esc_url(UAMS_SHORTCAKES_PATH.'assets/images/'.$icon_type.'/'.$icon_name.'.svg')).'</span>';

        } else {
            $icon_display = null;
        }

        $alertcontent = wp_kses_post($content);

        $customclass = implode(' ', array_map(sanitize_html_class(...), explode(',', $attrs['customclass'])));

        return sprintf(
            '<div class="uams-alert alert alert-%s %s" role="alert"><div class="alert-icon">%s</div><div class="alert-body">%s</div>%s</div>',
            $type,
            esc_attr($customclass),
            $icon_display,
            wpautop(urldecode($alertcontent)),
            $dismiss
        );
    }
}
