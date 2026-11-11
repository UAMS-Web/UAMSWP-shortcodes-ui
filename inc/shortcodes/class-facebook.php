<?php

declare(strict_types=1);

namespace UAMS_Shortcakes\Shortcodes;

class Facebook extends Shortcode
{
    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('Facebook', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-facebook',
            'attrs' => [
                [
                    'label' => esc_html__('URL', 'uams_shortcodes'),
                    'attr' => 'url',
                    'type' => 'text',
                    'description' => esc_html__('Full URL to the Facebook Post or Video', 'uams_shortcodes'),
                ],
            ],
        ];
    }

    public static function setup_actions(): void
    {
        add_action('shortcode_ui_after_do_shortcode', function ($shortcode): void {
            if (stripos($shortcode, '['.self::get_shortcode_tag()) !== false) {
                echo '<script src="'.esc_url('//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0').'"></script>';
            }
        });
    }

    /**
     * Facebook requires a bit extra massaging to make the embed responsive
     */
    public static function action_wp_footer(): void
    {
        ?>
		<div id="fb-root"></div>
		<script>
			(function($){
				$('.uams-shortcodes-responsive.fb-post').on('uams-shortcodes-responsive-resize', function(){
					var el = $(this);
					el.attr('data-width',el.css('width'));
					// Plugin has already loaded, so we need to reset the whole iframe.
					if ( el.attr('fb-iframe-plugin-query') ) {
						el.removeAttr('fb-iframe-plugin-query').removeAttr('fb-xfbml-state').empty();
						if ( FB && FB.XFBML && FB.XFBML.parse ) {
							FB.XFBML.parse();
						}
					}
				});
			}(jQuery))
		</script>
		<?php
    }

    public static function reversal($content)
    {

        /* Pattern for script-based Facebook embeds */
        // @pest-mutate-ignore: FalseToTrue
        if (stripos($content, '<script') !== false) {

            if (preg_match_all('#<div id="fb-root"></div><script>[^<]+</script><div class="fb-post" [^>]+href=[\'\"]([^\'\"]+)[\'\"].+</div>(</div>)?#', $content, $matches)) {
                $replacements = [];
                $shortcode_tag = self::get_shortcode_tag();
                foreach ($matches[0] as $key => $value) {
                    $replacements[$value] = '['.$shortcode_tag.' url="'.esc_url_raw($matches[1][$key]).'"]';
                }
                $content = self::make_replacements_to_content($content, $replacements);
            }

            /* Pattern for Facebook video embeds */
            if (preg_match_all('#<div id="fb-root"><\/div><script>[^<]+<\/script><div class="fb-video" [^>]+href=[\'\"][^\'\"]+[\'\"]><div class="fb-xfbml-parse-ignore"><blockquote cite=[\'\"][^\'\"]+[\'\"]><a href=[\'\"]([^\'\"]+)[\'\"]+.*?<\/div><\/div>?#', $content, $matches)) {
                $replacements = [];
                $shortcode_tag = self::get_shortcode_tag();
                foreach ($matches[0] as $key => $value) {
                    $replacements[$value] = '['.$shortcode_tag.' url="'.esc_url_raw('https://www.facebook.com'.$matches[1][$key]).'"]';
                }
                $content = self::make_replacements_to_content($content, $replacements);
            }
        }

        /* Pattern for iFrame Facebook embeds */
        $iframes = self::parse_iframes($content);
        if ($iframes) {
            $replacements = [];
            $matches = [];
            foreach ($iframes as $iframe) {
                if (self::parse_url($iframe->attrs['src'], PHP_URL_HOST) !== 'www.facebook.com') {
                    continue;
                }
                $possible_url = str_replace('https://www.facebook.com/plugins/post.php?href=', '', $iframe->attrs['src']);
                if (str_contains($possible_url, '&')) {
                    $possible_url = substr($possible_url, 0, strpos($possible_url, '&'));
                }
                $possible_url = urldecode($possible_url);
                if (self::parse_url($possible_url, PHP_URL_HOST) === 'www.facebook.com') {
                    $post_uri = $possible_url;
                } else {
                    continue;
                }
                $replacements[$iframe->original] = '['.self::get_shortcode_tag().' url="'.esc_url_raw($post_uri).'"]';
            }
            $content = self::make_replacements_to_content($content, $replacements);
        }

        return $content;
    }

    public static function callback($attrs, $content = ''): string
    {

        if (empty($attrs['url'])) {
            return '';
        }

        // kses converts & into &amp; and we need to undo this
        // See https://core.trac.wordpress.org/ticket/11311
        $attrs['url'] = str_replace('&amp;', '&', $attrs['url']);

        // Our matching URL patterns for Facebook
        $facebook_regex = [
            '#https?://(www)?\.facebook\.com/[^/]+/posts/[\d]+#',
            '#https?://(www)?\.facebook\.com\/video\.php\?v=[\d]+#',
            '#https?:\/\/www?\.facebook\.com\/+.*?\/videos\/[\d]+\/#',
            '#https?://(www)?\.facebook\.com\/permalink\.php\?story_fbid=[\d]+&id=[\d]+#',
            '#https?:\/\/www?\.facebook\.com\/.*?\/photos\/([^/]+)/([\d])+/#',
            '#https?:\/\/www?\.facebook\.com\/.*?\/videos\/([^/]+)/([\d])+/#',
            '#https?:\/\/www?\.facebook\.com\/groups\/([\d])+\/permalink/([\d])+/?#',
        ];

        $match = false;
        foreach ($facebook_regex as $regex) {
            if (preg_match($regex, $attrs['url'])) {
                $match = true;
            }
        }

        if (! $match) {
            if (current_user_can('edit_posts')) {
                /* translators: Invalid Facebook URL warning. */
                return '<div class="uams-shortcodes-error"><p>'.sprintf(esc_html__('Invalid Facebook URL: %s', 'uams_shortcodes'), esc_url($attrs['url'])).'</p></div>';
            }

            return '';
        }

        wp_enqueue_script('facebook-api', '//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0', [], false, true);
        if (! has_action('wp_footer', Facebook::class.'::action_wp_footer')) {
            add_action('wp_footer', Facebook::class.'::action_wp_footer');
        }

        return '<div class="fb-post uams-shortcodes-responsive" data-href="'.esc_url($attrs['url']).'" data-width="350px" data-true-height="550px" data-true-width="350px"><div class="fb-xfbml-parse-ignore"></div></div>';
    }
}
