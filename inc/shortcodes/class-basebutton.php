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
class BaseButton extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'color' => null,
        'btn_content' => null,
        'url' => null,
        'target' => null,
        'size' => null,
        'displayblock' => null,
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
            'label' => esc_html__('Basic Button', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-editor-removeformatting',
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
                'attr' => 'btncontent',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'Click Me!'
                )
            ),

            array(
                'label'        => esc_html__('Link Title', 'uams_shortcodes'),
                'attr'         => 'title',
                'type'         => 'text',
                'description'  => 'This text will appear as a title for the link.'
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
                'label' => esc_html__('Style and Color', 'uams_shortcodes'),
                'attr' => 'color',
                'type' => 'radio',
                'description' => 'For more information, see the <a href="https://brand.ncsu.edu/color/" target="_blank">NC State color palette</a>.',
                'encode' => false,
                'options' => array(
                    ''              => 'Basic / Gray',
                    'btn-default'   => 'Default / Ghost',
                    'btn-primary'   => 'Primary / UAMS Red',
                    'btn-success'   => 'Success / Green',
                    'btn-info'      => 'Info / Blue',
                    'btn-warning'   => 'Warning / Yellow',
                    'btn-danger'    => 'Danger / Orange',
                    'btn-link'      => 'Link / None',
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
                'label' => esc_html__('Display full-width?', 'uams_shortcodes'),
                'attr' => 'displayblock',
                'type' => 'checkbox',
                'description' => '',
                'encode' => false,
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

        $button_classes = array( 'btn', $attrs['color'], $attrs['size'] );

        if (!empty($attrs['displayblock'])) {
            $button_classes = array_merge( $button_classes, array('btn-block') );
        }

        if (!empty($attrs['customclass'])) {
            $customclasses = explode( ",", $attrs['customclass'] );
        }

        return sprintf(
            "<a href=\"%s\" title=\"%s\" target=\"%s\"><button type=\"button\" class=\"%s %s\">%s</button></a>",
            esc_url($attrs['url']),
            $attrs['title'],
            $attrs['target'],
            implode(' ', $customclasses),
            implode(' ', $button_classes),
            $attrs['btncontent']
        );
    }
}
