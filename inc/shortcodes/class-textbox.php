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
class TextBox extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'background' => 'none',
        'imagebg' => null,
        'txtcolor' => 'dark',
        'titletxt' => null,
        'fullscreen' => false,
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
            'label' => esc_html__('Text Box', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-info',
            'inner_content' => array(
                'label'        => esc_html__( 'Text Box Body', 'uams_shortcodes' ),
                'description'  => esc_html__( 'Main content of your text box. Text and basic HTML tags can be added here.', 'uams_shortcodes' ),
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
                'label' => esc_html__('Background Color', 'uams_shortcodes'),
                'attr' => 'background',
                'type' => 'select',
                'description' => 'See examples of <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/alerts/" target="_blank">each type of alert</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => array(
                    'none' => 'None',
                    'red' => 'Red',
                    'gray' => 'Gray',
                    'blue' => 'Blue',
                    'image' => 'Image',
                ),
            ),
            array(
                'label'       => esc_html__('Image Background', 'uams_shortcodes'),
                'attr'        => 'imagebg',
                'type'        => 'attachment',
                'description' => 'Used for image background only.',
                'libraryType' => array( 'image' ),
                'addButton'   => 'Select Image',
                'frameTitle'  => 'Select Image',
            ),
            array(
                'label'         => esc_html__('Text Color', 'uams_shortcodes'),
                'attr'          => 'txtcolor',
                'type'          => 'radio',
                    'options'   => array(
                       'dark'      => 'Dark',
                       'light'     => 'Light',
            ),
            'description'   => 'Choose the color of the text (Dark text over light colored images or light over darker images).',
            ),
            array(
                'label'        => esc_html__('Title', 'uams_shortcodes'),
                'attr'         => 'titletxt',
                'type'         => 'text',
                'description'  => 'The title heading of the box',
            ),
            array(
                'label' => esc_html__('Fullsreen?', 'uams_shortcodes'),
                'attr' => 'fullscreen',
                'type' => 'checkbox',
                'description' => '<strong>Note:</strong> This option only works on full width page template (No sidebar). You may get unintended results on pages with sidebar.',
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
        $attrs = array_merge(self::$defaults, $attrs);
        

        // Valid types only
        if (!empty($attrs['background'])) {
            $background = ( $attrs['background'] == 'none' || $attrs['background'] == 'red' || $attrs['background'] == 'gray' || $attrs['background'] == 'blue' || $attrs['background'] == 'image' ) ? sanitize_html_class( $attrs['background'] ) : 'none';
        } else {
            $background = 'none';
        }

        if ( $attrs['background'] == 'image' && $attrs['imagebg'] ) {
            $imagebg = 'style="background-image:url('. wp_kses_post( wp_get_attachment_image_url($attrs['imagebg'], 'Full') ) .');"';
        } else { $imagebg = null; }

        if ( !empty($attrs['txtcolor'])) {
            $txtcolor = ( $attrs['txtcolor'] == 'dark' || $attrs['txtcolor'] == 'light' ) ? sanitize_html_class( $attrs['txtcolor'] ) : 'dark';
        } else {
            $txtcolor = 'dark';
        }

        $titletxt = $attrs['titletxt'];

        if ( $attrs['fullscreen'] == true ) {
            $fullscreen = "uams-full-width";
        } else {
            $fullscreen = null;
        }

        $textcontent = wp_kses_post( $content );

        $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );

        return sprintf(
            "<div class=\"text-box-wrapper %s %s %s %s\" %s><h2 class=\"text-box-title\">%s</h2><div class=\"text-box-content\">%s</div></div>",
            $background,
            $txtcolor,
            $fullscreen,
            esc_attr( $customclass ),
            $imagebg,
            $titletxt,
            wpautop( urldecode( $textcontent ) )
        ); 
    }
}