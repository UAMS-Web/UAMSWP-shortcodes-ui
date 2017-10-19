<?php

/**
 * Lead Shortcode
 *
 * Inserts a Bootstrap class='lead' paragraph 
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Lead
 * @package UAMS_Shortcakes\Shortcodes
 */
class Lead extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
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
            'label' => esc_html__('Lead Paragraph', 'uams_shortcodes'),
            'listItemImage' => '<img src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'admin/images/icon-lead.png' ) . '" />',
            'inner_content' => array(
                'label'        => esc_html__( 'Lead Paragraph Text', 'uams_shortcodes' ),
                'description'  => esc_html__('', 'uams_shortcodes' ),
                'encode' => true,
            ),
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
     * return (not echo) our Lead element
     *
     * @return string
     */
    public static function callback($attrs, $content = '')
    {
        $attrs = (is_array($attrs))
            ? array_merge(self::$defaults, $attrs)
            : self::$defaults;

        $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );

        return sprintf(
            '<p class="lead %s">%s</p>',
            esc_attr( $customclass ),
            $content
        ); 
    }
}