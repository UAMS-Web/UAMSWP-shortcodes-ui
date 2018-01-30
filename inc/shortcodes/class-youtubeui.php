<?php

namespace UAMS_Shortcakes\Shortcodes;

class YouTubeUI extends Shortcode {

	private static $valid_hosts = array( 'www.youtube.com', 'youtube.com', 'youtu.be' );

	public static function get_shortcode_ui_args() {
		return array(
			'label'          => esc_html__( 'YouTube', 'uams_shortcodes' ),
			'listItemImage'  => '<img src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'assets/images/svg/icon-youtube.svg' ) . '" />',
			'attrs'          => array(
				array(
					'label'        => esc_html__( 'URL', 'uams_shortcodes' ),
					'attr'         => 'url',
					'type'         => 'text',
					'description'  => esc_html__( 'Full YouTube URL', 'uams_shortcodes' ),
				),
			),
		);
	}

	public static function reversal( $content ) {
		$iframes = self::parse_iframes( $content );
		if ( $iframes ) {
			$replacements = array();
			foreach ( $iframes as $iframe ) {
				if ( ! in_array( self::parse_url( $iframe->attrs['src'], PHP_URL_HOST ), self::$valid_hosts, true ) ) {
					continue;
				}
				if ( preg_match( '#youtube\.com/embed/([^/?]+)#', $iframe->attrs['src'], $matches ) ) {
					$embed_id = $matches[1];
				} else {
					continue;
				}
				$replacement_url                   = 'https://www.youtube.com/watch?v=' . $embed_id;
				$replacements[ $iframe->original ] = '[' . self::get_shortcode_tag() . ' url="' . esc_url_raw( $replacement_url ) . '"]';
			}
			$content = self::make_replacements_to_content( $content, $replacements );
		}

		return $content;
	}

	public static function callback( $attrs, $content = '' ) {

		$host = self::parse_url( $attrs['url'], PHP_URL_HOST );
		if ( empty( $attrs['url'] ) || ! in_array( $host, self::$valid_hosts, true ) ) {
			return '';
		}

		$list_id = '';

		if ( 'youtu.be' === $host ) { // Short url format: https://youtu.be/nc7F_qv3eI8
			$embed_id = trim( self::parse_url( $attrs['url'], PHP_URL_PATH ), '/' );

		} elseif ( in_array( $host, self::$valid_hosts, true ) ) { // https://www.youtube.com/watch?v=hDlpVFDmXrc
			$path = self::parse_url( $attrs['url'], PHP_URL_PATH );

			$query = self::parse_url( str_replace( array( '&amp;', '&#038;' ), '&', $attrs['url'] ), PHP_URL_QUERY );
			parse_str( $query, $args );
			if ( empty( $args['v'] ) ) {
				return '';
			}
			$embed_id = $args['v'];
			if ( ! empty( $args['list'] ) ) {
				$list_id = $args['list'];
			}
		}

		// ID is always the second part to the path
		$embed_url = 'https://youtube.com/embed/' . $embed_id;
		if ( ! empty( $list_id ) ) {
			$embed_url = add_query_arg( 'list', $list_id, $embed_url );
		}
		$embed_url = apply_filters( 'uams_shortcodes_youtube_embed_url', $embed_url, $attrs );
		return sprintf( '<div class="nc-video-player" role="region" aria-label="video" tabindex=-1><div class="tube-wrapper"><iframe class="shortcake-bakery-responsive" data-uams-youtube-type="single" data-uams-youtube="%s" width="640" height="360" frameborder="0" allowfullscreen="1" src="%s?rel=0"></iframe></div></div>', $embed_id, esc_url( $embed_url ) );
	}

}
