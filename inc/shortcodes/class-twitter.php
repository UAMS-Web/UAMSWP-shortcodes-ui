<?php

namespace UAMS_Shortcakes\Shortcodes;

class Twitter extends Shortcode {

	public static function get_shortcode_ui_args() {
		return array(
			'label'          => esc_html__( 'Twitter', 'uams_shortcodes' ),
			'listItemImage'  => '<img src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'assets/images/svg/icon-twitter.svg' ) . '" />',
			'attrs'          => array(
				array(
					'label'        => esc_html__( 'URL', 'uams_shortcodes' ),
					'attr'         => 'url',
					'type'         => 'text',
					'description'  => esc_html__( 'URL to a tweet', 'uams_shortcodes' ),
				),
			),
		);
	}

	public static function reversal( $content ) {

		if ( false === stripos( $content, '<script' ) ) {
			return $content;
		}

		$needle = '#<blockquote class="twitter-(tweet|video).+<a href="(https://twitter\.com/[^/]+/status/[^/]+)">.+(?=</blockquote>)</blockquote>\n?<script[^>]+src="//platform\.twitter\.com/widgets\.js"[^>]+></script>#';
		if ( preg_match_all( $needle, $content, $matches ) ) {
			$replacements = array();
			$shortcode_tag = self::get_shortcode_tag();
			foreach ( $matches[0] as $key => $value ) {
				$replacements[ $value ] = '[' . $shortcode_tag . ' url="' . esc_url_raw( $matches[2][ $key ] ) . '"]';
			}
			$content = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
		}

		return $content;
	}

	public static function callback( $attrs, $content = '' ) {

		if ( empty( $attrs['url'] ) ) {
			return '';
		}

		$needle = '#https?://twitter\.com/([^/]+)/status/([^/]+)#';
		if ( preg_match( $needle, $attrs['url'], $matches ) ) {
			$username = $matches[1];
		} else {
			return '';
		}

		return sprintf( '<blockquote class="twitter-tweet"><a href="%s">%s</a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>',
			esc_url( $attrs['url'] ),
			/* Translators: Tweet pretext. */
			sprintf( esc_html__( 'Tweet from @%s', 'uams_shortcodes' ), $username )
		);
	}

}
