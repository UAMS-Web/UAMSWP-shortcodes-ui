<?php

/**
 * Alerts Shortcode
 *
 * Generates a bootstrap-styled Alert
 * with lots of variety
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Alert
 * @package UAMS_Shortcakes\Shortcodes
 */
class IconBox extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'type' => 'info',
        'bgcolor' => 'none',
        'heading' => null,
        'headingicon' => null,
        'url' => null,
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
            'label' => esc_html__('Icon Box', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-info',
            'inner_content' => array(
                'label'        => esc_html__( 'Icon Box Body', 'uams_shortcodes' ),
                'description'  => esc_html__( 'Main content of your icon box. Text and basic HTML tags can be added here.', 'uams_shortcodes' ),
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
                'label' => esc_html__('Icon Box Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => 'See examples of <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/alerts/" target="_blank">each type of alert</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => array(
                    'default'   => 'Default',
                    'boxed'     => 'Boxed',
                    'framed'    => 'Framed',
                ),
            ),
            array(
                'label' => esc_html__('Background Color Option', 'uams_shortcodes'),
                'attr' => 'bgcolor',
                'type' => 'radio',
                'description' => 'See examples of <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/alerts/" target="_blank">each type of alert</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => array(
                    'none'      => 'Default',
                    'red'       => 'Red',
                    'gray'      => 'Gray',
                    'blue'      => 'Blue'
                ),
            ),
            array(
                'label' => esc_html__('Icon Box Heading', 'uams_shortcodes'),
                'attr' => 'heading',
                'type' => 'text',
                'description' => 'Title or heading for the box',
                'encode' => false,
            ),
            array(
                'label' => esc_html__('Heading Icon', 'uams_shortcodes'),
                'attr' => 'headingicon',
                'type' => 'select',
                'description' => 'Select any <a href="https://brand.ncsu.edu/bootstrap/components/" target="_blank">Bootstrap Glyphicon</a> or <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">WordPress Dashicon</a>.',
                'encode' => false,
                'options' => self::get_icon_collection(),
            ),
            array(
                'label' => esc_html__('URL', 'uams_shortcodes'),
                'attr' => 'url',
                'type' => 'url',
                'description' => 'Optional. Enter a URL to make your icon and title a clickable hyperlink.',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'http://www.uams.edu/'
                )
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
     * return (not echo) our Alert element
     *
     * @return string
     */
    public static function callback($attrs, $content = '')
    {
        $attrs = (is_array($attrs))
            ? array_merge(self::$defaults, $attrs)
            : self::$defaults;

        // Valid types only
        if (!empty($attrs['type'])) {
            $type = ( $attrs['type'] == 'default' || $attrs['type'] == 'boxed' || $attrs['type'] == 'framed' ) ? sanitize_html_class( $attrs['type'] ) : 'defualt';
        } else {
            $type = 'default';
        }

        // Valid types only
        if (!empty($attrs['bgcolor'])) {
            $bg = ( $attrs['bgcolor'] == 'none' || $attrs['bgcolor'] == 'red' || $attrs['bgcolor'] == 'gray' || $attrs['bgcolor'] == 'blue' ) ? sanitize_html_class( $attrs['bgcolor'] ) : 'none';
        } else {
            $bg = 'none';
        }

        $ignored_icons = self::get_ignored_icon_names();

        if (!empty($attrs['headingicon']) && !in_array($attrs['headingicon'], $ignored_icons)) {
            //$icon_type = explode('-', $attrs['headingicon'], 2)[0];
            //$icon_type = explode(' ', $icon_type, 2)[0]; // Changed a naming convention at one point. This extra step means old usages don't break.

            $icon_name = $attrs['headingicon'];
            //$icon_name = explode('-', $attrs['headingicon'], 2)[1]; //Original for SVG

            //$icon = '<span class="heading-icon" aria-hidden="true">'. self::uams_shortcodes_get_contents( esc_url(UAMS_SHORTCAKES_PATH . 'assets/images/'. $icon_type .'/'  . $icon_name . '.svg' ) ) .'</span>'; // Original SVG

            $icon = '<span class="'. $icon_name .'" aria-hidden="true"></span>';

        } else {
            $icon = null;
        }

        if (!empty($attrs['heading'])) {
            $heading = '<h3 class="icon-box-title">' . $attrs['heading'] . '</h3>';
        } else {
            $heading = null;
        }

        if (!empty($attrs['url'])) {
            $urlopen = '<a href="' . esc_url( $attrs['url'] ) . '">';
            $urlclose = '</a>';
        } else {
            $urlopen = '<span class="icon-wrap">';
            $urlclose = '</span>';
        }


        $txtcontent = wp_kses_post( $content );

        $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );

        return sprintf(
            "<div class=\"icon-box-wrapper %s %s %s\"><div class=\"icon-box-icon\">%s%s%s</div><div class=\"icon-box-content\">%s%s%s<div class=\"icon-box-description\">%s</div></div></div>",
            $type,
            $bg,
            esc_attr( $customclass ),
            $urlopen,
            $icon,
            $urlclose,
            $urlopen,
            $heading,
            $urlclose,
            wpautop( urldecode( $txtcontent ) )
        ); 
    }
}