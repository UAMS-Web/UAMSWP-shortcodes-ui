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
class Alert extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'type' => 'info',
        'dismiss' => false,
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
            'label' => esc_html__('Alert', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-info',
            'inner_content' => array(
                'label'        => esc_html__( 'Alert Body', 'uams_shortcodes' ),
                'description'  => esc_html__( 'Main content of your alert. Text and basic HTML tags can be added here.', 'uams_shortcodes' ),
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
                'label' => esc_html__('Alert Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => 'See examples of <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/alerts/" target="_blank">each type of alert</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => array(
                    'success' => 'Success',
                    'info' => 'Information',
                    'help' => 'Help',
                    'warning' => 'Warning',
                    'danger' => 'Danger',
                ),
            ),
            array(
                'label' => esc_html__('Dismissable?', 'uams_shortcodes'),
                'attr' => 'dismiss',
                'type' => 'checkbox',
                'description' => '',
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
        if (!empty($attrs['type'])) {
            $type = ( $attrs['type'] == 'success' || $attrs['type'] == 'info' || $attrs['type'] == 'help' || $attrs['type'] == 'warning' || $attrs['type'] == 'danger' ) ? sanitize_html_class( $attrs['type'] ) : 'info';
        } else {
            $type = 'info';
        }

        switch ($type) {
            case 'success':
                $icon = 'glyphicon-checkbox';
                break;
            
            case 'info':
                $icon = 'dashicons-info';
                break;
            
            case 'help':
                $icon = 'glyphicon-question';
                break;
            
            case 'warning':
                $icon = 'dashicons-warning';
                break;

            case 'danger':
                $icon = 'glyphicon-x';
                break;

            default:
                $icon = 'dashicons-info';
                break;
        }

        if ( $attrs['dismiss'] == true ) {
            $dismiss = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        } else { $dismiss = null; }

        if (!empty( $icon )) {
            $icon_type = explode('-', $icon, 2)[0];
            $icon_name = explode('-', $icon, 2)[1];

            $icon_display = '<span aria-hidden="true">'. self::uams_shortcodes_get_contents( esc_url(UAMS_SHORTCAKES_PATH . 'assets/images/'. $icon_type .'/'  . $icon_name . '.svg' ) ) .'</span>';

        } else {
            $icon_display = null;
        }

        $alertcontent = wp_kses_post( $content );

        $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );

        return sprintf(
            "<div class=\"uams-alert alert alert-%s %s\" role=\"alert\"><div class=\"alert-icon\">%s</div><div class=\"alert-body\">%s</div>%s</div>",
            $type,
            esc_attr( $customclass ),
            $icon_display,
            wpautop( urldecode( $alertcontent ) ),
            $dismiss
        ); 
    }
}