<?php

namespace UAMS_Shortcakes\Shortcodes;

class Iframe extends Shortcode {

	public static function get_shortcode_ui_args() {
		return array(
			'label'          => esc_html__( 'Iframe', 'uams_shortcodes' ),
			'listItemImage'  => 'dashicons-admin-site',
			'attrs'          => array(
				array(
					'label'        => esc_html__( 'URL', 'uams_shortcodes' ),
					'attr'         => 'src',
					'type'         => 'text',
					'description'  => esc_html__( 'Full URL to the iFrame source. Host must be whitelisted.', 'uams_shortcodes' ),
				),
				array(
					'label'        => esc_html__( 'Height', 'uams_shortcodes' ),
					'attr'         => 'height',
					'type'         => 'number',
					'description'  => esc_html__( 'Pixel height of the iframe. Defaults to 600.', 'uams_shortcodes' ),
				),
				array(
					'label'        => esc_html__( 'Width', 'uams_shortcodes' ),
					'attr'         => 'width',
					'type'         => 'number',
					'description'  => esc_html__( 'Pixel width of the iframe. Defaults to 670.', 'uams_shortcodes' ),
				),
				array(
					'label'        => esc_html__( 'Disable Responsiveness', 'uams_shortcodes' ),
					'attr'         => 'disableresponsiveness',
					'type'         => 'checkbox',
					'description'  => esc_html__( 'By default, height/width ratio of iframe will be maintained regardless of container width. Check this to keep constant height/width.', 'uams_shortcodes' ),
				),
			),
		);
	}


	/**
	*
	* Get the whitelisted iframe domains for the plugin
	* Whitelist domains using `add_filter` on this hook to return array of your site's whitelisted domaiins.
	*
	* @return array of whitelisted domains, e.g. 'assets.yourdomain.com'
	*/
	public static function get_whitelisted_iframe_domains() {
		return apply_filters( 'uams_shortcodes_whitelisted_iframe_domains', array() );
	}

	/**
	 * Transform any <iframe> embeds within content to our iframe shortcode
	 *
	 * @param string $content
	 * @return string
	 */
	public static function reversal( $content ) {
		$iframes = self::parse_iframes( $content );
		if ( $iframes ) {
			$whitelisted_iframe_domains = static::get_whitelisted_iframe_domains();
			$replacements = array();
			foreach ( $iframes as $iframe ) {
				if ( ! in_array( self::parse_url( $iframe->attrs['src'], PHP_URL_HOST ), $whitelisted_iframe_domains, true ) ) {
					continue;
				}
				$replacements[ $iframe->original ] = '[' . self::get_shortcode_tag() . ' src="' . esc_url_raw( $iframe->attrs['src'] ) . '"]';
			}
			$content = self::make_replacements_to_content( $content, $replacements );
		}

		return $content;
	}

	public static function callback( $attrs, $content = '' ) {
		if ( empty( $attrs['src'] ) ) {
			return '';
		}

		$defaults = array(
			'height'                  => 600,
			'width'                   => 670,
			'disableresponsiveness'   => false,
			);
		$attrs = array_merge( $defaults, $attrs );

		// Allow iFrame URLs to be filtered
		$attrs['src'] = apply_filters( 'uams_shortcodes_iframe_src', $attrs['src'], $attrs );

		$host = self::parse_url( $attrs['src'], PHP_URL_HOST );
		if ( ! in_array( $host, static::get_whitelisted_iframe_domains(), true ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				/* translators: Invalid hostname warning. */
				return '<div class="uams-shortcodes-error"><p>' . sprintf( esc_html__( 'Invalid hostname in URL: %s', 'uams_shortcodes' ), esc_url( $attrs['src'] ) ) . '</p></div>';
			} else {
				return '';
			}
		}

		if ( $attrs['disableresponsiveness'] ) {
			$class = '';
		} else {
			$class = 'uams-shortcodes-responsive';
		}

		return sprintf(
			'<iframe src="%s" width="%s" height="%s" data-true-width="%s" data-true-height="%s" frameborder="0" scrolling="no" class="%s"></iframe>',
			esc_url( $attrs['src'] ),
			esc_attr( $attrs['width'] ),
			esc_attr( $attrs['height'] ),
			esc_attr( $attrs['width'] ),
			esc_attr( $attrs['height'] ),
			esc_attr( $class )
		);
	}

}
