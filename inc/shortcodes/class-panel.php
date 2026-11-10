<?php

/**
 * Panel Shortcode
 *
 * Inserts a Bootstrap panel, either collapsible or not
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Panel
 */
class Panel extends Shortcode
{
    /**
     * declare default attributes
     */
    private static array $defaults = [
        'heading' => null,
        'headingtype' => 'p',
        'type' => 'panel-default',
        'state' => 'static',
        'style' => 'bootstrap',
        'customclass' => null,
    ];

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('Collapsible Panel', 'uams_shortcodes'),
            'listItemImage' => '<img src="'.esc_url(UAMS_SHORTCAKES_URL_ROOT.'admin/images/icon-panel.png').'" />',
            'inner_content' => [
                'label' => esc_html__('Panel Body', 'uams_shortcodes'),
                'description' => esc_html__('', 'uams_shortcodes'),
                'encode' => true,
            ],
            'attrs' => self::get_attributes(),
        ];
    }

    /**
     * provide UI items for shortcake
     */
    public static function get_attributes(): array
    {
        return [
            [
                'label' => esc_html__('Panel Heading', 'uams_shortcodes'),
                'attr' => 'heading',
                'type' => 'text',
                'description' => '',
                'encode' => false,
            ],
            [
                'label' => esc_html__('Heading Type', 'uams_shortcodes'),
                'attr' => 'headingtype',
                'type' => 'radio',
                'description' => 'Headings <a href="https://accessibility.oit.ncsu.edu/training/accessibility-handbook/headings.html" target="_blank">help determine page structure</a>, and are used by screen reading software as landmarks for navigating page content. If you are using this callout as a page title or heading for a section on the page, choose the appropriate heading. Otherwise, choose "Paragraph."',
                'encode' => false,
                'options' => [
                    'p' => 'Paragraph',
                    'h4' => 'Heading 4',
                    'h3' => 'Heading 3',
                    'h2' => 'Heading 2',
                ],
            ],
            [
                'label' => esc_html__('Panel Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => [
                    'panel-default' => 'Default (10% Gray)',
                    'panel-primary' => 'Primary (Wolfpack Red)',
                    'panel-success' => 'Success (Genomic Green)',
                    'panel-info' => 'Info (Innovation Blue)',
                    'panel-warning' => 'Warning (Hunt Yellow)',
                    'panel-danger' => 'Danger (Reynolds Red)',
                ],
            ],
            [
                'label' => esc_html__('Behavior & State', 'uams_shortcodes'),
                'attr' => 'state',
                'type' => 'radio',
                'description' => '',
                'options' => [
                    'static' => 'Static',
                    'open' => 'Collapsible and open when page loads',
                    'closed' => 'Collapsible and closed when page loads',
                ],
            ],
            [
                'label' => esc_html__('Style', 'uams_shortcodes'),
                'attr' => 'style',
                'type' => 'radio',
                'description' => '',
                'options' => [
                    'boostrap' => 'Classic Bootstrap',
                    'noborder' => 'Classic Bootstrap, no border',
                    'simple' => 'Simple',
                ],
            ],
            [
                'label' => esc_html__('Custom CSS classes', 'uams_shortcodes'),
                'attr' => 'customclass',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Comma-separated list',
                ],
            ],
        ];
    }

    /**
     * return (not echo) our Panel element
     */
    public static function callback($attrs, $content = ''): string
    {
        $attrs = array_merge(self::$defaults, $attrs);

        // Only headings or p. Otherwise, default to p.
        if (! empty($attrs['headingtype'])) {
            $headingtype = (in_array($attrs['headingtype'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) ? sanitize_html_class($attrs['headingtype']) : 'p';
        } else {
            $headingtype = 'p';
        }

        if (! empty($attrs['type'])) {
            $type = (in_array($attrs['type'], ['panel-primary', 'panel-success', 'panel-info', 'panel-warning', 'panel-danger'])) ? sanitize_html_class($attrs['type']) : 'panel-default';
        } else {
            $type = 'panel-default';
        }

        // @pest-mutate-ignore: EqualToIdentical
        if ($attrs['style'] == 'noborder') {
            $style = 'noborder';
            // @pest-mutate-ignore: EqualToIdentical
        } elseif ($attrs['style'] == 'simple') {
            $style = 'simple';
        } else {
            $style = null;
        }

        if (! empty($attrs['heading'])) {
            $id = self::text_to_css_friendly($attrs['heading']);
            // @pest-mutate-ignore: IncrementInteger, DecrementInteger
            $id = $id.'-'.random_int(0, mt_getrandmax()); // Add a random integer to the ID, in case users have multiple panels with the same heading. They shouldn't, but if they do collapsible behavior gets weird.
        } else {
            $id = null;
        }

        // @pest-mutate-ignore: NotEqualToNotIdentical
        if ($attrs['state'] != 'static') {

            // @pest-mutate-ignore: EqualToIdentical
            if ($attrs['state'] == 'closed') {
                $collapsed = 'collapsed';
            } else {
                $collapsed = null;
            }

            $heading = sprintf(
                '<button data-toggle="collapse" data-target="#%s" class="%s">%s<span class="showless"><span aria-hidden="true">%s</span> Show Less</span><span class="showmore"><span aria-hidden="true">%s</span> Show More</span></button>',
                $id,
                $collapsed,
                wp_kses_post($attrs['heading']),
                self::uams_shortcodes_get_contents(esc_url(UAMS_SHORTCAKES_PATH.'assets/images/glyphicon/minus.svg')),
                self::uams_shortcodes_get_contents(esc_url(UAMS_SHORTCAKES_PATH.'assets/images/glyphicon/plus-no-bkgrnd.svg'))
            );
        } else {
            $id = null;
            $heading = sprintf(
                '<span>%s</span>',
                sanitize_text_field($attrs['heading'])
            );
        }

        // @pest-mutate-ignore: NotEqualToNotIdentical
        if ($attrs['state'] != 'closed') {
            $in = 'in';
        } else {
            $in = null;
        }

        $panelcontent = wp_kses_post($content);

        $customclass = implode(' ', array_map(sanitize_html_class(...), explode(',', $attrs['customclass'])));

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
            esc_attr($type),
            esc_attr($style),
            esc_attr($customclass),
            esc_html($headingtype),
            $heading,
            esc_html($headingtype),
            $id,
            $in,
            wpautop(urldecode($panelcontent))
        );
    }
}
