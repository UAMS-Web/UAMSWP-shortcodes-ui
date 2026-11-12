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
class Button extends Shortcode
{
    /**
     * declare default attributes
     */
    private static array $defaults = [
        'color' => null,
        'text' => '',
        'url' => null,
        'target' => null,
        'size' => null,
        'icon' => null,
        'type' => null,
        'newline' => false,
        'customclass' => null,
    ];

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('UAMS Button', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-plus-alt',
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
                'attr' => 'text',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Click Me!',
                ],
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
                'label' => esc_html__('Color', 'uams_shortcodes'),
                'attr' => 'color',
                'type' => 'radio',
                'description' => 'For more information, see the <a href="#" target="_blank">Coming Soon</a>.',
                'encode' => false,
                'options' => [
                    '' => 'Default',
                    'btn-red' => 'Red',
                    'btn-gray' => 'Gray',
                    'btn-green' => 'Green',
                    'btn-blue' => 'Blue',
                    'btn-yellow' => 'Yellow',
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
                'label' => esc_html__('Icon', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'select',
                'encode' => false,
                'options' => [
                    'btn-go' => 'Default',
                    'btn-plus' => 'Plus',
                    'btn-external' => 'External',
                    'btn-play' => 'Play',
                    'btn-pdf' => 'PDF',
                ],
            ],
            [
                'label' => esc_html__('New line?', 'uams_shortcodes'),
                'attr' => 'newline',
                'type' => 'checkbox',
                'description' => 'This adds a line break after the button to move it to the next line.',
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

        $button_classes = ['btn', $attrs['color'], $attrs['size'], $attrs['type']];

        if ($attrs['newline'] == true) {
            $newline = '<br/>';
        } else {
            $newline = null;
        }

        if (! empty($attrs['customclass'])) {
            $customclasses = array_map(sanitize_html_class(...), explode(',', $attrs['customclass']));
        }

        // Only a browsing-context name. Otherwise, leave the target empty.
        $target = in_array($attrs['target'], ['_blank', '_self', '_top', '_parent'], true) ? $attrs['target'] : '';

        return sprintf(
            '<a class="uams-btn %s %s" href="%s" target="%s">%s</a>%s',
            esc_attr(implode(' ', $button_classes)),
            esc_attr(implode(' ', $customclasses)),
            esc_url($attrs['url']),
            $target,
            wp_kses_post($attrs['text']),
            $newline
        );

        // return  $shortcode;//'<div class="uams-button '. esc_attr( $customclass ) .'">' . do_shortcode($shortcode) . '</div>';
    }
}
