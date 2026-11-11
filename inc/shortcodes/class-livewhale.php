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
 */
class LiveWhale extends Shortcode
{
    /**
     * declare default attributes
     */
    private static array $defaults = [
        'view' => 'all',
        'group' => null,
        'type' => null,
        'audience' => null,
        'tags' => null,
        'exclude_tags' => null,
        'only_starred' => null,
        'include_repeats' => false,
        'max' => 5,
        'width' => 200,
        'height' => 200,
        'customclass' => null,
    ];

    const URL = 'https://calendar.uams.edu';

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('UAMS Calendar', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-calendar-alt',
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
                'label' => esc_html__('Default View', 'uams_shortcodes'),
                'attr' => 'view',
                'type' => 'select',
                'description' => '',
                'encode' => false,
                'options' => [
                    'all' => 'List',
                    'day' => 'Day',
                    'week' => 'Week',
                    'month' => 'Month',
                ],
            ],
            [
                'label' => esc_html__('Group', 'uams_shortcodes'),
                'attr' => 'group',
                'type' => 'select',
                'description' => '',
                'encode' => false,
                'options' => [
                    '' => 'All',
                    'Admin' => 'Admin',
                    'Faculty' => 'Faculty',
                    'Main Calendar' => 'Main Calendar',
                    'Public' => 'Public',
                ],
            ],
            [
                'label' => esc_html__('Event Type(s)', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Comma-separated list',
                ],
            ],
            [
                'label' => esc_html__('Audience', 'uams_shortcodes'),
                'attr' => 'audience',
                'type' => 'select',
                'description' => '',
                'encode' => false,
                'options' => [
                    '' => 'All',
                    'Faculty' => 'Faculty',
                    'Staff' => 'Staff',
                    'Parents' => 'Parents',
                    'Students' => 'Students',
                ],
            ],
            [
                'label' => esc_html__('Tag(s)', 'uams_shortcodes'),
                'attr' => 'tags',
                'type' => 'text',
                'description' => 'Must be in all tags (AND)',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Comma-separated list',
                ],
            ],
            [
                'label' => esc_html__('Exclude Tag(s)', 'uams_shortcodes'),
                'attr' => 'exclude_tags',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => 'Comma-separated list',
                ],
            ],
            [
                'label' => esc_html__('Only Starred', 'uams_shortcodes'),
                'attr' => 'only_starred',
                'type' => 'checkbox',
                'description' => '',
            ],
            [
                'label' => esc_html__('Include Repeats', 'uams_shortcodes'),
                'attr' => 'include_repeats',
                'type' => 'checkbox',
                'description' => 'Show all instances of repeating/multi-day event',
            ],
            [
                'label' => esc_html__('Max number of items', 'uams_shortcodes'),
                'attr' => 'max',
                'type' => 'number',
                'description' => '',
                'encode' => false,
                'meta' => [
                    'placeholder' => esc_html__('5'),
                    'min' => '1',
                    'step' => '1',
                ],
            ],
            // array(
            //     'label' => esc_html__('Thumbnail width', 'uams_shortcodes'),
            //     'attr' => 'width',
            //     'type' => 'number',
            //     'description' => '',
            //     'encode' => false,
            //     'meta'   => array(
            //         'placeholder' 	=> esc_html__( '200' ),
            //         'min'			=> '50',
            //         'step'			=> '1',
            //     ),
            // ),
            // array(
            //     'label' => esc_html__('Thumbnail height', 'uams_shortcodes'),
            //     'attr' => 'height',
            //     'type' => 'number',
            //     'description' => '',
            //     'encode' => false,
            //     'meta'   => array(
            //         'placeholder' 	=> esc_html__( '200' ),
            //         'min'			=> '50',
            //         'step'			=> '1',
            //     ),
            // ),
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
     * LiveWhale CORS script
     */
    public function lw_wp_footer(): void
    {
        global $defaultview, $group, $type, $audience, $tags, $excludetags, $starred, $repeats, $max, $width, $height, $customclasses;
        ?>
        <script>
            lwCalendar({
                host: '<?php echo self::URL; ?>',
                theme: 'global',
                default_view: '<?php echo $defaultview; ?>',
                widget_args: {
                    hide_repeats: <?php echo $repeats; ?>,
                    only_starred: <?php echo $starred; ?>,
                    mini_cal_heat_map: true,
                    thumb_width: <?php echo $width; ?>,
                    thumb_height: <?php echo $height; ?>,
                    <?php echo $max; ?>
                    development: true,
                    <?php echo $tags; ?>
                    <?php echo $excludetags; ?>
                }
            });
        </script>
        <?php
    }

    /**
     * return (not echo) our button element
     *
     * @param  array  $attrs
     * @param  string  $content
     */
    public static function callback($attrs, $content = ''): string
    {
        $attrs = (is_array($attrs))
            ? array_merge(self::$defaults, $attrs)
            : self::$defaults;

        global $defaultview, $group, $type, $audience, $tags, $excludetags, $starred, $repeats, $max, $width, $height, $customclasses;

        $customclasses = [];

        if (isset($attrs['view'])) {
            $defaultview = esc_attr($attrs['view']);
        }

        if (isset($attrs['group'])) {
            $group = esc_attr($attrs['group']);
        }

        if (isset($attrs['type'])) {
            $type = $attrs['type'];
        }

        if (isset($attrs['audience'])) {
            $audience = $attrs['audience'];
        }

        // Each tag lands in a single-quoted string in the footer script, so escape it for that context.
        $tags = null;
        if (! empty($attrs['tags'])) {
            $tags = array_map(esc_js(...), explode(',', $attrs['tags']));
            $tags = "'".implode("','", $tags)."'";
            $tags = 'tag: ['.$tags.'],';
        }

        $excludetags = null;
        if (! empty($attrs['exclude_tags'])) {
            $excludetags = array_map(esc_js(...), explode(',', $attrs['exclude_tags']));
            $excludetags = "'".implode("','", $excludetags)."'";
            $excludetags = 'exclude_tag: ['.$excludetags.'],';
        }

        $starred = 'false';
        if ($attrs['only_starred'] == true) {
            $starred = 'true';
        }

        $repeats = 'true';
        if ($attrs['include_repeats'] == true) {
            $repeats = 'false'; // include_repeat(false) = hide_repeat
        }

        if (isset($attrs['max'])) {
            // @pest-mutate-ignore: NotEqualToNotIdentical
            if ($defaultview != 'month') {
                $max = 'max: '.intval($attrs['max']).',';
            }
        }
        $width = '200';
        if ($attrs['width']) {
            $width = intval($attrs['width']);
        }

        $height = '200';
        if ($attrs['height']) {
            $height = intval($attrs['height']);
        }

        if (! empty($attrs['customclass'])) {
            $customclasses = array_map(sanitize_html_class(...), explode(',', $attrs['customclass']));
        }

        $return = '';

        if ($customclasses !== []) {
            // @pest-mutate-ignore: ConcatEqualToEqual
            $return .= '<div class="'.implode(' ', $customclasses).'">';
        }

        // @pest-mutate-ignore: NotEqualToNotIdentical
        if ($defaultview != 'all') {
            $return .= '<!-- The Day, Week, Month, All Upcoming Events tabs -->
                        <div id="lw_cal_view_selector"></div>';
        }

        $return .= '
        <!-- Calendar container of your events list -->
        <div id="lw_cal">
            <div id="lw_cal_header"></div>
            <div id="lw_cal_body" class="lw_clearfix">
                <div id="lw_cal_events"></div>
            </div>
        </div>';
        if ($customclasses !== []) {
            $return .= '</div>';
        }

        // if ( ! has_action( 'wp_footer', 'lw_wp_footer' ) ) {
        add_action('wp_footer', LiveWhale::class.'::lw_wp_footer', 30);
        // }
        /* Enqueue two required scripts */
        wp_enqueue_script('googlemaps-api', 'https://maps.googleapis.com/maps/api/js?sensor=false', [], false, false);
        wp_enqueue_script('livewhale-js', 'https://uams-dev.lwcal.com/livewhale/scripts/cors-calendar.js', [], false, false);

        return $return;

        // <!-- The Day, Week, Month, All Upcoming Events tabs -->
        // <div id="lw_cal_view_selector"></div>
        // <!-- Calendar container of your events list -->
        // <div id="lw_cal">
        //    <div id="lw_cal_header"></div>
        //    <div id="lw_cal_body" class="lw_clearfix">
        //      <div id="lw_cal_events"></div>
        //    </div>
        // </div>
    }
}
