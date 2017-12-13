<?php

/**
 * ThreeColumn Shortcode
 *
 * Inserts a Bootstrap row with three columns
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class ThreeColumn
 * @package UAMS_Shortcakes\Shortcodes
 */
class ThreeColumn extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'col1' => null,
        'col2' => null,
        'col3' => null,
        'ratio' => 'third-third-third',
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
            'label' => esc_html__('Row, 3 Columns', 'uams_shortcodes'),
            'listItemImage' => '<img src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'admin/images/icon-threecolumn.png' ) . '" />',
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
                'label' => esc_html__('Column 1', 'uams_shortcodes'),
                'attr' => 'col1',
                'type' => 'textarea',
                'description' => 'Basic HTML supported. Line breaks automatically become new paragraphs.',
                'encode' => true,
            ),
            array(
                'label' => esc_html__('Column 2', 'uams_shortcodes'),
                'attr' => 'col2',
                'type' => 'textarea',
                'description' => 'Basic HTML supported. Line breaks automatically become new paragraphs.',
                'encode' => true,
            ),
            array(
                'label' => esc_html__('Column 3', 'uams_shortcodes'),
                'attr' => 'col3',
                'type' => 'textarea',
                'description' => 'Basic HTML supported. Line breaks automatically become new paragraphs.',
                'encode' => true,
            ),
            array(
                'label' => esc_html__('Column Width Ratio', 'uams_shortcodes'),
                'attr' => 'ratio',
                'type' => 'radio',
                'description' => 'Choose the widths for your columns. On mobile devices, columns stack vertically (left on top of right).',
                'encode' => false,
                'options' => array(
                    'third-third-third' => '33% | 33% | 33%',
                    'quarter-quarter-half' => '25% | 25% | 50%',
                    'quarter-half-quarter' => '25% | 50% | 25%',
                    'half-quarter-quarter' => '50% | 25% | 25%',
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
     * return (not echo) our Three Column element
     *
     * @return string
     */
    public static function callback($attrs, $content = '')
    {
        $attrs = array_merge(self::$defaults, $attrs);

        switch ($attrs['ratio']) {
            case 'third-third-third':
                $col1 = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
                $col2 = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
                $col3 = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
                break;

            case 'quarter-quarter-half':
                $col1 = 'col-lg-3 col-md-3 col-sm-3 col-xs-12';
                $col2 = 'col-lg-3 col-md-3 col-sm-3 col-xs-12';
                $col3 = 'col-lg-6 col-md-6 col-sm-6 col-xs-12';
                break;

            case 'quarter-half-quarter':
                $col1 = 'col-lg-3 col-md-3 col-sm-3 col-xs-12';
                $col2 = 'col-lg-6 col-md-6 col-sm-6 col-xs-12';
                $col3 = 'col-lg-3 col-md-3 col-sm-3 col-xs-12';
                break;

            case 'half-quarter-quarter':
                $col1 = 'col-lg-6 col-md-6 col-sm-6 col-xs-12';
                $col2 = 'col-lg-3 col-md-3 col-sm-3 col-xs-12';
                $col3 = 'col-lg-3 col-md-3 col-sm-3 col-xs-12';
                break;
            
            default:
                $col1 = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
                $col2 = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
                $col3 = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
        }

        $contentcol1 = wp_kses_post( $attrs['col1'] );
        $contentcol2 = wp_kses_post( $attrs['col2'] );
        $contentcol3 = wp_kses_post( $attrs['col3'] );

        $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );

        return sprintf(
            '<div class="row %s">
                <div class="inner-container">
                    <div class="%s">
                        %s
                    </div>
                    <div class="%s">
                        %s
                    </div>
                    <div class="%s">
                        %s
                    </div>
                </div>
            </div>',
            esc_attr( $customclass ),
            $col1,
            wpautop( do_shortcode( urldecode( $contentcol1 ) ) ),
            $col2,
            wpautop( do_shortcode( urldecode( $contentcol2 ) ) ),
            $col3,
            wpautop( do_shortcode( urldecode( $contentcol3 ) ) )
        ); 
    }
}