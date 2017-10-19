<?php

namespace UAMS_Shortcakes\Shortcodes;

class Image_Comparison extends Shortcode {

	public static function get_shortcode_ui_args() {
		return array(
			'label'          => esc_html__( 'Image Comparison', 'uams_shortcodes' ),
			'listItemImage'  => 'dashicons-format-gallery',
			'attrs'          => array(
				array(
					'label'  => esc_html__( 'Left Image', 'uams_shortcodes' ),
					'attr'   => 'left',
					'type'   => 'attachment',
					'libraryType' => array( 'image' ),
					'addButton'   => esc_html__( 'Select Image', 'uams_shortcodes' ),
					'frameTitle'  => esc_html__( 'Select Image', 'uams_shortcodes' ),
					),
				array(
					'label'  => esc_html__( 'Right Image', 'uams_shortcodes' ),
					'attr'   => 'right',
					'type'   => 'attachment',
					'libraryType' => array( 'image' ),
					'addButton'   => esc_html__( 'Select Image', 'uams_shortcodes' ),
					'frameTitle'  => esc_html__( 'Select Image', 'uams_shortcodes' ),
					),
				array(
					'label'  => esc_html__( 'Slider Start Position', 'uams_shortcodes' ),
					'attr'   => 'position',
					'type'   => 'select',
					'options' => array(
						'center' => esc_html__( 'Center', 'uams_shortcodes' ),
						'mostlyleft' => esc_html__( 'Mostly Left', 'uams_shortcodes' ),
						'mostlyright' => esc_html__( 'Mostly Right', 'uams_shortcodes' ),
					),
				),
			),
		);
	}

	public static function setup_actions() {
		add_action( 'wp_enqueue_scripts', 'UAMS_Shortcakes\Shortcodes\Image_Comparison::action_init_register_scripts' );
		add_action( 'shortcode_ui_after_do_shortcode', function( $shortcode ) {
			if ( false !== stripos( $shortcode, '[' . self::get_shortcode_tag() ) ) {
				echo '<link rel="stylesheet" href="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'assets/css/juxtapose.css' ) . '">';
				echo '<script type="text/javascript" src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'assets/js/juxtapose.js' ) . '"></script>';
			}
		});
	}

	public static function action_init_register_scripts() {
		wp_register_script( 'juxtapose-js', UAMS_SHORTCAKES_URL_ROOT . 'assets/js/juxtapose.js', array( 'jquery' ) );
		wp_register_style( 'juxtapose-css', UAMS_SHORTCAKES_URL_ROOT . 'assets/css/juxtapose.css' );
	}

	public static function callback( $attrs, $content = '' ) {
		if ( empty( $attrs['left'] ) || empty( $attrs['right'] ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<div class="uams-shortcodes-error"><p>' . esc_html__( 'Two images required for image comparison.', 'uams_shortcodes' ) . '</p></div>';
			} else {
				return '';
			}
		}

		if ( empty( $attrs['position'] ) ) {
			 $attrs['position'] = 'center';
		}

		switch ( $attrs['position'] ) {

			case 'center' :
				$attrs['position'] = 50;
				break;

			case 'mostlyleft' :
				$attrs['position'] = 10;
				break;

			case 'mostlyright' :
				$attrs['position'] = 90;
				break;

		}

		$left_image = wp_get_attachment_image_src( $attrs['left'], 'large', false );
		$right_image = wp_get_attachment_image_src( $attrs['right'], 'large', false );

		$left_caption = get_post_field( 'post_excerpt', $attrs['left'] );
		$right_caption = get_post_field( 'post_excerpt', $attrs['right'] );

		$left_meta = wp_get_attachment_metadata( $attrs['left'] );
		$right_meta = wp_get_attachment_metadata( $attrs['right'] );
		$left_credit = $left_meta['image_meta']['credit'];
		$right_credit = $right_meta['image_meta']['credit'];

		if ( ! $left_image || ! $right_image ) {
			return;
		}

		wp_enqueue_script( 'juxtapose-js' );
		wp_enqueue_style( 'juxtapose-css' );

		/* Begin container */
		$out = '<section class="image-comparison">';
		$out .= '<div class="juxtapose" data-startingposition="';
		$out .= esc_attr( $attrs['position'] );
		$out .= '" data-showlabels="true" data-showcredits="true" data-animate="true">';

		/* Left Image */
		$out .= '<img src="';
		$out .= esc_url( $left_image[0] );
		$out .= '" data-label="';
		$out .= esc_attr( $left_caption );
		$out .= '" data-credit="';
		$out .= esc_attr( $left_credit );
		$out .= '">';

		/* Right Image */
		$out .= '<img src="';
		$out .= esc_url( $right_image[0] );
		$out .= '" data-label="';
		$out .= esc_attr( $right_caption );
		$out .= '" data-credit="';
		$out .= esc_attr( $right_credit );
		$out .= '">';

		/* Close container */
		$out .= '</div>';
		$out .= '</section>';

		return $out;

	}

}
