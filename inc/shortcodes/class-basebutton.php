<?php

declare(strict_types=1);

/**
 * Buttons Shortcode
 *
 * Generates a bootstrap-styled button
 * with lots of variety
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Button
 */
class BaseButton extends Shortcode
{
    /**
     * declare default attributes
     */
    private static array $defaults = [
        'color' => null,
        'btncontent' => '',
        'title' => '',
        'url' => null,
        'target' => null,
        'size' => null,
        'displayblock' => null,
        'customclass' => null,
    ];

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('Basic Button', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-editor-removeformatting',
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
                'label' => esc_html__('Button Text', 'uams_shortcodes'),
                'attr' => 'btncontent',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Click Me!',
                ],
            ],

            [
                'label' => esc_html__('Link Title', 'uams_shortcodes'),
                'attr' => 'title',
                'type' => 'text',
                'description' => 'This text will appear as a title for the link.',
            ],

            [
                'label' => esc_html__('Destination URL', 'uams_shortcodes'),
                'attr' => 'url',
                'type' => 'url',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'http://www.uams.edu/',
                ],
            ],

            [
                'label' => esc_html__('Target', 'uams_shortcodes'),
                'attr' => 'target',
                'type' => 'radio',
                'options' => [
                    '_top' => 'Same Tab/Window',
                    '_blank' => 'New Tab/Window',
                ],
            ],

            [
                'label' => esc_html__('Style and Color', 'uams_shortcodes'),
                'attr' => 'color',
                'type' => 'radio',
                'description' => 'For more information, see the <a href="https://brand.ncsu.edu/color/" target="_blank">NC State color palette</a>.',
                'encode' => false,
                'options' => [
                    '' => 'Basic / Gray',
                    'btn-default' => 'Default / Ghost',
                    'btn-primary' => 'Primary / UAMS Red',
                    'btn-success' => 'Success / Green',
                    'btn-info' => 'Info / Blue',
                    'btn-warning' => 'Warning / Yellow',
                    'btn-danger' => 'Danger / Orange',
                    'btn-link' => 'Link / None',
                ],
            ],
            [
                'label' => esc_html__('Size', 'uams_shortcodes'),
                'attr' => 'size',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => [
                    '' => 'Default',
                    'btn-lg' => 'Large',
                    'btn-sm' => 'Small',
                ],
            ],
            [
                'label' => esc_html__('Display full-width?', 'uams_shortcodes'),
                'attr' => 'displayblock',
                'type' => 'checkbox',
                'description' => '',
                'encode' => false,
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
     * return (not echo) our button element
     *
     * @param  array  $attrs
     * @param  string  $content
     */
    public static function callback($attrs, $content = ''): string
    {
        $attrs = (is_array($attrs))
            ? array_merge(self::$defaults, $attrs)
            : self::$defaults;

        $customclasses = [];

        $button_classes = ['btn', $attrs['color'], $attrs['size']];

        if (! empty($attrs['displayblock'])) {
            $button_classes = array_merge($button_classes, ['btn-block']);
        }

        if (! empty($attrs['customclass'])) {
            $customclasses = array_map(sanitize_html_class(...), explode(',', $attrs['customclass']));
        }

        // Only a browsing-context name. Otherwise, leave the target empty.
        $target = in_array($attrs['target'], ['_blank', '_self', '_top', '_parent'], true) ? $attrs['target'] : '';

        return sprintf(
            '<a href="%s" title="%s" target="%s"><button type="button" class="%s %s">%s</button></a>',
            esc_url($attrs['url']),
            esc_attr($attrs['title']),
            $target,
            esc_attr(implode(' ', $customclasses)),
            esc_attr(implode(' ', $button_classes)),
            wp_kses_post($attrs['btncontent'])
        );
    }
}
