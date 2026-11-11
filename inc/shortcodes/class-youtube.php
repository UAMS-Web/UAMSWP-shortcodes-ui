<?php

declare(strict_types=1);

namespace UAMS_Shortcakes\Shortcodes;

class YouTube extends Shortcode
{
    private static array $valid_hosts = ['www.youtube.com', 'youtube.com', 'youtu.be'];

    public static function get_shortcode_ui_args(): array
    {
        return [
            'label' => esc_html__('YouTube', 'uams_shortcodes'),
            'listItemImage' => '<img src="'.esc_url(UAMS_SHORTCAKES_URL_ROOT.'assets/images/svg/icon-youtube.svg').'" />',
            'attrs' => [
                [
                    'label' => esc_html__('URL', 'uams_shortcodes'),
                    'attr' => 'url',
                    'type' => 'text',
                    'description' => esc_html__('Full YouTube URL', 'uams_shortcodes'),
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

                if (preg_match('#youtube\.com/embed/([^/?]+)#', $iframe->attrs['src'], $matches)) {
                    $embed_id = $matches[1];
                } else {
                    continue;
                }

                $replacement_url = 'https://www.youtube.com/watch?v='.$embed_id;
                $replacements[$iframe->original] = '['.self::get_shortcode_tag().' url="'.esc_url_raw($replacement_url).'"]';
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

        $host = self::parse_url($attrs['url'], PHP_URL_HOST);
        if (! in_array($host, self::$valid_hosts, true)) {
            return '';
        }

        $list_id = '';

        if ($host === 'youtu.be') { // Short url format: https://youtu.be/nc7F_qv3eI8
            $embed_id = trim((string) self::parse_url($attrs['url'], PHP_URL_PATH), '/');
            if ($embed_id === '') {
                return '';
            }

        } elseif (in_array($host, self::$valid_hosts, true)) { // https://www.youtube.com/watch?v=hDlpVFDmXrc
            $path = self::parse_url($attrs['url'], PHP_URL_PATH);

            $query = self::parse_url(str_replace(['&amp;', '&#038;'], '&', $attrs['url']), PHP_URL_QUERY);
            parse_str((string) $query, $args);
            if (empty($args['v'])) {
                return '';
            }

            $embed_id = $args['v'];
            if (! empty($args['list'])) {
                $list_id = $args['list'];
            }
        }

        // ID is always the second part to the path
        $embed_url = 'https://youtube.com/embed/'.$embed_id;
        if (! empty($list_id)) {
            $embed_url = add_query_arg('list', $list_id, $embed_url);
        }

        $embed_url = apply_filters('uams_shortcodes_youtube_embed_url', $embed_url, $attrs);

        return sprintf('<iframe class="shortcake-bakery-responsive" width="640" height="360" src="%s" frameborder="0"></iframe>', esc_url($embed_url));
    }
}
