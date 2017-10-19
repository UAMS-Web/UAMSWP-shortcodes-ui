<?php

/**
 * Panel Shortcode
 *
 * Inserts a Bootstrap panel, either collapsible or not 
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Panel
 * @package UAMS_Shortcakes\Shortcodes
 */
class Panel extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'heading' => null,
        'headingtype' => 'p',
        'type' => 'panel-default',
        'state' => 'static',
        'style' => 'bootstrap',
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
            'label' => esc_html__('Collapsible Panel', 'uams_shortcodes'),
            'listItemImage' => '<img src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'admin/images/icon-panel.png' ) . '" />',
            'inner_content' => array(
                'label'        => esc_html__( 'Panel Body', 'uams_shortcodes' ),
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
                'label' => esc_html__('Panel Heading', 'uams_shortcodes'),
                'attr' => 'heading',
                'type' => 'text',
                'description' => '',
                'encode' => false,
            ),
            array(
                'label' => esc_html__('Heading Type', 'uams_shortcodes'),
                'attr' => 'headingtype',
                'type' => 'radio',
                'description' => 'Headings <a href="https://accessibility.oit.ncsu.edu/training/accessibility-handbook/headings.html" target="_blank">help determine page structure</a>, and are used by screen reading software as landmarks for navigating page content. If you are using this callout as a page title or heading for a section on the page, choose the appropriate heading. Otherwise, choose "Paragraph."',
                'encode' => false,
                'options' => array(
                    'p' => 'Paragraph',
                    'h4' => 'Heading 4',
                    'h3' => 'Heading 3',
                    'h2' => 'Heading 2',
                ),
            ),
            array(
                'label' => esc_html__('Panel Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => array(
                    'panel-default' => 'Default (10% Gray)',
                    'panel-primary' => 'Primary (Wolfpack Red)',
                    'panel-success' => 'Success (Genomic Green)',
                    'panel-info' => 'Info (Innovation Blue)',
                    'panel-warning' => 'Warning (Hunt Yellow)',
                    'panel-danger' => 'Danger (Reynolds Red)',
                ),
            ),
            array(
                'label' => esc_html__('Behavior & State', 'uams_shortcodes'),
                'attr' => 'state',
                'type' => 'radio',
                'description' => '',
                'options' => array(
                    'static' => 'Static',
                    'open' => 'Collapsible and open when page loads',
                    'closed' => 'Collapsible and closed when page loads'
                ),
            ),
            array(
                'label' => esc_html__('Style', 'uams_shortcodes'),
                'attr' => 'style',
                'type' => 'radio',
                'description' => '',
                'options' => array(
                    'boostrap' => 'Classic Bootstrap',
                    'noborder' => 'Classic Bootstrap, no border',
                    'simple' => 'Simple'
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
     * return (not echo) our Panel element
     *
     * @return string
     */
    public static function callback($attrs, $content = '')
    {
        $attrs = array_merge(self::$defaults, $attrs);

        // Only headings or p. Otherwise, default to p.
        if (!empty($attrs['headingtype'])) {
            $headingtype = ( $attrs['headingtype'] == 'h1' || $attrs['headingtype'] == 'h2' || $attrs['headingtype'] == 'h3' || $attrs['headingtype'] == 'h4' || $attrs['headingtype'] == 'h5' || $attrs['headingtype'] == 'h6' || $attrs['headingtype'] == 'p' ) ? sanitize_html_class( $attrs['headingtype'] ) : 'p';
        } else {
            $headingtype = 'p';
        }

        if (!empty($attrs['type'])) {
            $type = ( $attrs['type'] == 'panel-default' || $attrs['type'] == 'panel-primary' || $attrs['type'] == 'panel-success' || $attrs['type'] == 'panel-info' || $attrs['type'] == 'panel-warning' || $attrs['type'] == 'panel-danger' ) ? sanitize_html_class( $attrs['type'] ) : 'panel-default';
        } else {
            $type = 'panel-default';
        }

        if ( $attrs['style'] == 'noborder' ) {
            $style = 'noborder';
        } elseif ( $attrs['style'] == 'simple' ) {
            $style = 'simple';
        } else {
            $style = null;
        }

        if ( !empty($attrs['heading']) ) {
            $id = self::text_to_css_friendly( $attrs['heading'] );
            $id = $id .'-'. rand(); // Add a random integer to the ID, in case users have multiple panels with the same heading. They shouldn't, but if they do collapsible behavior gets weird.
        } else {
            $id = null;
        }

        if ( $attrs['state'] != 'static' ) {

            if ( $attrs['state'] == 'closed' ) {
                $collapsed = 'collapsed';
            } else {
                $collapsed = null;
            }

            $heading = sprintf(
                '<button data-toggle="collapse" data-target="#%s" class="%s">%s<span class="showless"><span aria-hidden="true">%s</span> Show Less</span><span class="showmore"><span aria-hidden="true">%s</span> Show More</span></button>',
                $id,
                $collapsed,
                $attrs['heading'],
                self::uams_shortcodes_get_contents( esc_url(UAMS_SHORTCAKES_PATH . 'assets/images/glyphicon/minus.svg' ) ),
                self::uams_shortcodes_get_contents( esc_url(UAMS_SHORTCAKES_PATH . 'assets/images/glyphicon/plus-no-bkgrnd.svg' ) )
                );
        } else {
            $id = null;
            $heading = sprintf(
                '<span>%s</span>',
                sanitize_text_field( $attrs['heading'] )
                );
        }

        if ( $attrs['state'] != 'closed' ) {
            $in = 'in';
        } else {
            $in = null;
        }

        $panelcontent = wp_kses_post( $content );

        $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );

        return sprintf(
            '<div class="panel %s %s %s">
                <div class="panel-heading">
                    <%s class="panel-title">%s</%s>
                </div>
                <div id="%s" class="panel-collapse collapse %s">
                    <div class="panel-body">
                        %s
                    </div>
                </div>
            </div>',
            esc_attr( $type ),
            esc_attr( $style ),
            esc_attr( $customclass ),
            esc_html( $headingtype ),
            $heading,
            esc_html( $headingtype ),
            $id,
            $in,
            wpautop( urldecode( $panelcontent ) )
        ); 
    }
}