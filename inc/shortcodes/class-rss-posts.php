<?php

/**
 * RSS Posts Shortcode
 *
 * Embed n Recent Posts feed
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class RSS_Posts
 */
class RSS_Posts extends Shortcode
{
    /**
     * declare default attributes
     */
    private static array $defaults = [
        'feed' => null,
        'number' => '3',
        'display' => 'grid',
        'customclass' => null,
    ];

    /**
     * gets the arguments needed for shortcake
     */
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('RSS Posts', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-rss',
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
                'label' => esc_html__('RSS Feed URL', 'uams_shortcodes'),
                'attr' => 'feed',
                'type' => 'text',
                'description' => 'Enter an RSS feed URL. <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/rss-posts/" target="_blank">Learn more</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
            ],
            [
                'label' => esc_html__('Number of Posts to Display', 'uams_shortcodes'),
                'attr' => 'number',
                'type' => 'number',
                'description' => '',
            ],
            [
                'label' => esc_html__('Display Post Author', 'uams_shortcodes'),
                'attr' => 'author',
                'type' => 'checkbox',
                'description' => 'This will only display authors for content types that support authors.',
            ],
            [
                'label' => esc_html__('Display Style', 'uams_shortcodes'),
                'attr' => 'display',
                'type' => 'radio',
                'description' => 'Select a WordPress content type. Some content types may not make sense to embed here, depending on how you use them on your website. <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/recent-posts/" target="_blank">Learn more</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => [
                    'grid' => 'Grid',
                    'list' => 'List',
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
     * return (not echo) our Alert element
     */
    public static function callback($attrs, $content = ''): string
    {
        $attrs = array_merge(self::$defaults, $attrs);

        // Only grid or list. Otherwise, default to grid.
        $display = ($attrs['display'] == 'grid' || $attrs['display'] == 'list') ? sanitize_text_field($attrs['display']) : 'grid';

        // Only integers. Otherwise, default to 3.
        $posts_per_page = (ctype_digit($attrs['number'])) ? sanitize_text_field($attrs['number']) : '3';

        $customclass = implode(' ', array_map(sanitize_html_class(...), explode(',', $attrs['customclass'])));

        include_once ABSPATH.WPINC.'/feed.php';

        // Only an http(s) URL that passes WordPress' own checks (no local or private hosts) is fetched.
        $feed = empty($attrs['feed']) || ! is_string($attrs['feed']) ? false : wp_http_validate_url($attrs['feed']);

        // Get a SimplePie feed object from the specified feed source.
        $rss = ($feed === false) ? false : fetch_feed($feed);

        $maxitems = 0;

        if ($rss !== false && ! is_wp_error($rss)) {
            // Checks that the object is created correctly
            // Figure out how many total items there are, but limit it to 5.
            $maxitems = $rss->get_item_quantity($posts_per_page);
            // Build an array of all the items, starting with element 0 (first element).
            $rss_items = $rss->get_items(0, $maxitems);
        }

        $output = '';

        if ($maxitems == 0) {
            $output .= 'No items';
        } else {

            if ($attrs['display'] == 'list') {
                $col = 'col-md-12';
            } elseif ($attrs['display'] == 'grid') {
                $col = 'col-lg-4 col-md-4 col-sm-12 col-xs-12';
            }

            foreach ($rss_items as $item) {

                $post_cat = '<span class="rss-posts-meta-sep" aria-hidden="true">|</span> <span class="rss-posts-cat">'.$item->get_category()->get_label().'</span>';

                if ($attrs['author']) {
                    $byline = sprintf(
                        '<p class="recent-post-byline">By %s</p>',
                        $item->get_author()->get_name()
                    );
                } else {
                    $byline = null;
                }

                $output .= sprintf(
                    '<a href="%s" class="recent-post %s"><p class="recent-post-meta">%s %s</p><p class="recent-post-title">%s</p>%s<p class="recent-post-excerpt">%s %s</p></a>',
                    $item->get_permalink(),
                    $col,
                    $item->get_date('M j, Y'),
                    $post_cat,
                    $item->get_title(),
                    $byline,
                    $item->get_content(),
                    '<span class="right-arrow" aria-hidden="true">'.self::uams_shortcodes_get_contents(esc_url(UAMS_SHORTCAKES_PATH.'assets/images/glyphicon/thin-arrow-right.svg')).'</span>'
                );
            }

        }

        return sprintf(
            '<div class="recent-posts row %s %s">%s</div>',
            esc_attr($display),
            esc_attr($customclass),
            $output
        );

    }
}
