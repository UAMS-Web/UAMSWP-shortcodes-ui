<?php

/**
 * Calendar Shortcode (LiveWhale)
 *
 * Generates a calendar widget
 * from calendar.uams.edu [Livewhale]
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Calendar
 * @package UAMS_Shortcakes\Shortcodes
 */
class LiveWhale_Widget extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'id' => null,
        'customclass' => null
    );

    const URL = 'https://calendar.uams.edu';

    /**
     * gets the arguments needed for shortcake
     *
     * @return array
     */
    public static function get_shortcode_ui_args()
    {
        return array(
            'label' => esc_html__('UAMS Calendar Widget', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-calendar',
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
                'label' => esc_html__('ID Number of Widget', 'uams_shortcodes'),
                'attr' => 'id',
                'type' => 'number',
                'description' => 'ex. ID=2  <code>(&lt;div class="lwcw" data-options="id=<b>2</b>&format=html"&gt;)</code>',
                'encode' => false,
                'meta'   => array(
                    'min'			=> '1',
                    'step'			=> '1',
                ),
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
        
        global $id, $customclasses;

        $customclasses = array();
        
        if (isset($attrs['id'])){
            $id = esc_attr($attrs['id']);
        }

        if (!empty($attrs['customclass'])) {
            $customclasses = explode( ",", $attrs['customclass'] );
        }

        $return = '';
        
        if ($customclasses){
            $return .= '<div class="' . $customclasses . '">';
        }

        $return .= '
        <!-- Livewhale Calendar Widget -->
        <div class="lwcw" data-options="id='. $id .'&format=html"></div> 
        <script type="text/javascript" id="lw_lwcw" src="'. self::URL .'/livewhale/theme/core/scripts/lwcw.js"></script>';
        if ($customclasses){
            $return .= '</div>';
        }

        /* Enqueue two required scripts */
        // wp_enqueue_script( 'lw_lwcw', self::URL .'/livewhale/theme/core/scripts/lwcw.js', array(), false, false );        

        return $return;

    }
}
