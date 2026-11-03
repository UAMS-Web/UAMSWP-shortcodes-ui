<?php

/**
 * ProgressBar Shortcode
 *
 * Inserts a customizable Bootstrap progress bar
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class ProgressBar
 * @package UAMS_Shortcakes\Shortcodes
 */
class ProgressBar extends Shortcode
{

    /**
     * declare default attributes
     */
    private static array $defaults = [
        'complete' => null,
        'type' => 'progress-bar-default',
        'style' => 'default',
        'customclass' => null
    ];

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('Progress Bar', 'uams_shortcodes'),
            'listItemImage' => '<img src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'admin/images/icon-progressbar.png' ) . '" />',
            'attrs' => self::get_attributes()
        ];
    }

    /**
     * provide UI items for shortcake
     */
    public static function get_attributes(): array
    {
        return [
            [
                'label' => esc_html__('Percentage Complete', 'uams_shortcodes'),
                'attr' => 'complete',
                'type' => 'number',
                'description' => 'Enter the numerical value only (no % sign).',
                'encode' => false,
            ],
            [
                'label' => esc_html__('Progress Bar Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => [
                    'progress-bar-primary'  => 'Primary / UAMS Red',
                    'progress-bar-success'  => 'Success / Green',
                    'progress-bar-info'     => 'Info / Blue',
                    'progress-bar-warning'  => 'Warning / Yellow',
                    'progress-bar-danger'   => 'Danger / Orange',
                ],
            ],
            [
                'label' => esc_html__('Style', 'uams_shortcodes'),
                'attr' => 'style',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => [
                    'default' => 'Default',
                    'display-value' => 'Display Percentage Complete Value',
                    'progress-striped' => 'Striped',
                    'progress-striped active' => 'Striped and Animated',
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
                ]
            ]
        ];
    }
    
    /**
     * return (not echo) our Progress Bar element
     */
    public static function callback($attrs, $content = ''): string
    {
        $attrs = array_merge(self::$defaults, $attrs);

        switch ($attrs['style']) {
            case 'default':
                $style = null;
                $sr = 'sr-only';
                break;

            case 'display-value':
                $style = null;
                $sr = null;
                break;

            case 'progress-striped':

            case 'progress-striped active':
                $style = $attrs['style'];
                $sr = 'sr-only';
                break;
            
            default:
                $style = null;
                $sr = 'sr-only';
        }

        // Only integers. Otherwise, default to 50.
        $complete = ( ctype_digit( $attrs['complete'] ) ) ? sanitize_text_field( $attrs['complete'] ) : '50';

        // Valid types only.
        if (!empty($attrs['type'])) {
            $type = ( in_array($attrs['type'], ['progress-bar-primary', 'progress-bar-success', 'progress-bar-info', 'progress-bar-warning', 'progress-bar-danger']) ) ? sanitize_html_class( $attrs['type'] ) : 'progress-bar-primary';
        } else {
            $type = 'progress-bar-primary';
        }

        $customclass = implode(" ", array_map( sanitize_html_class(...), explode( ",", $attrs['customclass'] ) ) );

        return sprintf(
            '<div class="progress %s %s">
                <div class="progress-bar %s" role="progressbar" aria-valuenow="%d" aria-valuemin="0" aria-valuemax="100" style="width: %d%%;">
                    <span class="%s">%d%%</span> <span class="sr-only">Complete</span>
                </div>
            </div>',
            $style,
            esc_attr( $customclass ),
            $attrs['type'],
            $complete,
            $complete,
            $sr,
            $complete
        ); 
    }
}