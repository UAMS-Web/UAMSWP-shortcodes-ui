<?php

/**
 * Buttons Shortcode
 *
 * Generates a bootstrap-styled button
 * with lots of variety
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Button
 * @package UAMS_Shortcakes\Shortcodes
 */
class Button extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'color' => null,
        'btntext' => null,
        'url' => null,
        'target' => null,
        'size' => null,
        'icon' => null,
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
            'label' => esc_html__('UAMS Button', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-plus',
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
                'label' => esc_html__('Button Text', 'uams_shortcodes'),
                'attr' => 'text',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'Click Me!'
                )
            ),
            array(
                'label' => esc_html__('Destination URL', 'uams_shortcodes'),
                'attr' => 'url',
                'type' => 'url',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'http://www.uams.edu/'
                )
            ),
            array(
                'label' => esc_html__('Target', 'uams_shortcodes'),
                'attr' => 'target',
                'type' => 'radio',
                'options' => array(
                    '_top' => 'Same Tab/Window',
                    '_blank' => 'New Tab/Window', 
                ),
            ),
            array(
                'label' => esc_html__('Color', 'uams_shortcodes'),
                'attr' => 'color',
                'type' => 'radio',
                'description' => 'For more information, see the <a href="#" target="_blank">Coming Soon</a>.',
                'encode' => false,
                'options' => array(
                    ''          => 'Default',
                    'btn-red'       => 'Red',
                    'btn-gray'      => 'Gray',
                    'btn-green'     => 'Green',
                    'btn-blue'      => 'Blue',
                    'btn-yellow'    => 'Yellow',
                )
            ),
            array(
                'label' => esc_html__('Size', 'uams_shortcodes'),
                'attr' => 'size',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => array(
                    ''          => 'Default',
                    'btn-lg'    => 'Large',
                    'btn-sm'    => 'Small',
                )
            ),
            array(
                'label' => esc_html__('Icon', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'select',
                'encode' => false,
                'options' => array(
                    'btn-go'        => 'Default',
                    'btn-plus'      => 'Plus',
                    'btn-external'  => 'External',
                    'btn-play'      => 'Play',
                )
            ),
            array(
                'label' => esc_html__('New line?', 'uams_shortcodes'),
                'attr' => 'newline',
                'type' => 'checkbox',
                'description' => 'This adds a line break after the button to move it to the next line.',
            ),
            array(
                'label' => esc_html__('Custom CSS classes', 'uams_shortcodes'),
                'attr' => 'customclass',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'Comma-separated list'
                )
            )
        );
    }

    /**
     * return (not echo) our button element
     *
     * @param array $attrs
     * @param string $content
     * @return string
     */
    public static function callback($attrs, $content = '')
    {
        $attrs = (is_array($attrs))
            ? array_merge(self::$defaults, $attrs)
            : self::$defaults;
        
        $customclasses = array();

         $button_classes = array( 'btn', $attrs['color'], $attrs['size'], $attrs['type'] );

         if ( $attrs['newline'] == true ) {
            $newline = "<br/>";
        } else {
            $newline = null;
        }

        if (!empty($attrs['customclass'])) {
            $customclasses = explode( ",", $attrs['customclass'] );
        }

        return sprintf(
            "<a class=\"uams-btn %s %s\" href=\"%s\" target=\"%s\">%s</a>%s",
            implode(' ', $button_classes),
            implode(' ', $customclasses),
            esc_url($attrs['url']),
            $attrs['target'],
            $attrs['text'],
            $newline
        );

        //return  $shortcode;//'<div class="uams-button '. esc_attr( $customclass ) .'">' . do_shortcode($shortcode) . '</div>';
    }
}
