<?php

declare(strict_types=1);

/**
 * Pullquote Shortcode
 *
 * Generates a left-, right-, or center-aligned pullquote in various colors
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Pullquote
 */
class Pullquote extends Shortcode
{
    /**
     * declare default attributes
     */
    private static array $defaults = [
        'cite' => null,
        'color' => 'uamsred',
        'align' => 'alignleft',
        'customclass' => null,
    ];

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('Pullquote', 'uams_shortcodes'),
            'listItemImage' => '<img src="'.esc_url(UAMS_SHORTCAKES_URL_ROOT.'admin/images/icon-pullquote.png').'" />',
            'inner_content' => [
                'label' => esc_html__('Quote Body', 'uams_shortcodes'),
                'description' => __('Enter the text that you\'re quoting. No HTML allowed. <p><strong>NOTE:</strong> Pullquotes are assumed to be visual enhancements only---that is, the text that you\'re highlighting with a pullquote already appears somewhere in your normal content.</p><p>With that in mind, pullquotes are "hidden" from certain assistive technologies (like screenreaders), so as to avoid confusing users with repeated quotes given out of context. Do not enter any quotes that do not appear elsewhere in your post or page text.</p>', 'uams_shortcodes'),
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
                'label' => esc_html__('Citation', 'uams_shortcodes'),
                'attr' => 'cite',
                'type' => 'text',
                'description' => 'Optional. Enter the name of the person who said the quote.',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Optional',
                ],
            ],
            [
                'label' => esc_html__('Text Color', 'uams_shortcodes'),
                'attr' => 'color',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => self::get_accessible_text_brand_colors(),
            ],
            [
                'label' => esc_html__('Alignment', 'uams_shortcodes'),
                'attr' => 'align',
                'type' => 'radio',
                'description' => '',
                'options' => [
                    'alignleft' => 'Left',
                    'aligncenter' => 'Center',
                    'alignright' => 'Right',
                ],
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
     * return (not echo) our Pullquote element
     */
    public static function callback($attrs, $content = ''): string
    {
        $attrs = array_merge(self::$defaults, $attrs);

        if (! empty($attrs['cite'])) {
            $cite = '<cite>'.sanitize_text_field($attrs['cite']).'</cite>';
        } else {
            $cite = null;
        }

        if (in_array($attrs['align'], ['aligncenter', 'alignright'])) {
            $align = sanitize_html_class($attrs['align']);
        } else {
            $align = 'alignleft';
        }

        if (! empty($attrs['color']) && array_key_exists($attrs['color'], self::get_accessible_text_brand_colors())) {
            $color = sanitize_html_class($attrs['color']);
        } else {
            $color = 'reynoldsred';
        }

        $customclass = implode(' ', array_map(sanitize_html_class(...), explode(',', $attrs['customclass'])));

        return sprintf(
            '<aside class="pullquote %s %s" aria-hidden="true"><p class="quote-content %s">%s</p>%s</aside>',
            $align,
            esc_attr($customclass),
            esc_attr($color),
            sanitize_text_field($content),
            $cite
        );
    }
}
