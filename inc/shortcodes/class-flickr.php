<?php

namespace UAMS_Shortcakes\Shortcodes;

class Flickr extends Shortcode
{
    private static array $valid_hosts = ['flickr.com', 'www.flickr.com'];

    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('Flickr', 'uams_shortcodes'),
            'listItemImage' => '<img src="'.esc_url(UAMS_SHORTCAKES_URL_ROOT.'assets/images/svg/icon-flickr.svg').'" />',
            'attrs' => [
                [
                    'label' => esc_html__('URL', 'uams_shortcodes'),
                    'attr' => 'url',
                    'type' => 'text',
                    'description' => esc_html__('URL to a Flickr gallery', 'uams_shortcodes'),
                ],
            ],
        ];
    }

    public static function reversal($content)
    {
        $iframes = self::parse_iframes($content);
        if ($iframes) {
            $replacements = [];
            foreach ($iframes as $iframe) {
                if (! in_array(self::parse_url($iframe->attrs['src'], PHP_URL_HOST), self::$valid_hosts, true)) {
                    continue;
                }

                $url = preg_replace('#/player/?$#', '/', $iframe->attrs['src']);
                $replacements[$iframe->original] = '['.self::get_shortcode_tag().' url="'.esc_url_raw($url).'"]';
            }

            $content = self::make_replacements_to_content($content, $replacements);
        }

        return $content;
    }

    public static function callback($attrs, $content = ''): string
    {

        if (empty($attrs['url']) || ! in_array(self::parse_url($attrs['url'], PHP_URL_HOST), self::$valid_hosts, true)) {
            return '';
        }

        // Append /player/ to the URL if it's not already there
        if (stripos(substr($attrs['url'], strlen($attrs['url']) - 8), '/player') === false) {
            $attrs['url'] = rtrim($attrs['url'], '/').'/player/';
        }

        return sprintf('<iframe class="uams-shortcodes-responsive" width="500" height="334" src="%s" frameborder="0"></iframe>', esc_url($attrs['url']));
    }
}
