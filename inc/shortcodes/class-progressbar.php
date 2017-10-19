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
     *
     * @var array
     */
    private static $defaults = array(
        'complete' => null,
        'type' => 'progress-bar-default',
        'style' => 'default',
        'customclass' => null
    );

    /**
     * gets the arguments needed for shortcake
     *
     * @return array
     */
    public static function get_shortcode_ui_args()
    {
        return array(
            'label' => esc_html__('Progress Bar', 'uams_shortcodes'),
            'listItemImage' => '<img src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'admin/images/icon-progressbar.png' ) . '" />',
            'attrs' => self::get_attributes()
        );
    }

    /**
     * provide UI items for shortcake
     *
     * @return array
     */
    public static function get_attributes()
    {
        return array(
            array(
                'label' => esc_html__('Percentage Complete', 'uams_shortcodes'),
                'attr' => 'complete',
                'type' => 'number',
                'description' => 'Enter the numerical value only (no % sign).',
                'encode' => false,
            ),
            array(
                'label' => esc_html__('Progress Bar Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => array(
                    'progress-bar-primary'  => 'Primary / UAMS Red',
                    'progress-bar-success'  => 'Success / Green',
                    'progress-bar-info'     => 'Info / Blue',
                    'progress-bar-warning'  => 'Warning / Yellow',
                    'progress-bar-danger'   => 'Danger / Orange',
                ),
            ),
            array(
                'label' => esc_html__('Style', 'uams_shortcodes'),
                'attr' => 'style',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => array(
                    'default' => 'Default',
                    'display-value' => 'Display Percentage Complete Value',
                    'progress-striped' => 'Striped',
                    'progress-striped active' => 'Striped and Animated',
                ),
            ),
            array(
                'label' => esc_html__('Custom CSS classes', 'uams_shortcodes'),
                'attr' => 'customclass',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'Comma-separated list',
                )
            )
        );
    }
    
    /**
     * return (not echo) our Progress Bar element
     *
     * @return string
     */
    public static function callback($attrs, $content = '')
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
                $style = $attrs['style'];
                $sr = 'sr-only';
                break;

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
            $type = ( $attrs['type'] == 'progress-bar-primary' || $attrs['type'] == 'progress-bar-success' || $attrs['type'] == 'progress-bar-info' || $attrs['type'] == 'progress-bar-warning' || $attrs['type'] == 'progress-bar-danger' ) ? sanitize_html_class( $attrs['type'] ) : 'progress-bar-primary';
        } else {
            $type = 'progress-bar-primary';
        }

        $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );

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