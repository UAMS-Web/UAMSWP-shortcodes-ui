<?php
/*
 * Plugin Name: UAMSWP Shortcodes UI
 * Version: 0.4
 * Plugin URI: 
 * Description: Useful shortcodes for UAMS websites, which can be added using the Shortcake shortcode UI.
 * Author: Todd McKee, MEd, Original: OIT Design, Brian DeConinck, Chris Deaton
 * Author URI: https://www.uams.edu/
 * Requires at least: 4.6
 * Tested up to: 4.8.1
 *
 * @package WordPress
 * @since 0.3.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'is_plugin_active' ) )
     require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
// This plugin uses namespaces and requires PHP 5.3 or greater.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', create_function( '',
	"echo '<div class=\"error\"><p>" . __( 'UAMSWP Shortcode UI requires PHP 5.3 to function properly. Please upgrade PHP or deactivate the plugin.', 'uamswp-shortcode-ui' ) . "</p></div>';" ) );
	return;
} elseif ( ! is_plugin_active( 'shortcode-ui/shortcode-ui.php') ) {
	add_action( 'admin_notices', create_function( '',
	"echo '<div class=\"error\"><p>" . __( 'UAMSWP Shortcode UI requires <a href="https://wordpress.org/plugins/shortcode-ui/">Shortcake (Shortcode UI)</a> plugin to be active!', 'uamswp-shortcode-ui' ) . "</p></div>';" ) );
	return;
} else {
	require_once dirname(__FILE__) . '/inc/class-uams-shortcodes.php';

	define('UAMS_SHORTCAKES_VERSION', '0.4');
	define('UAMS_SHORTCAKES_URL_ROOT', plugin_dir_url(__FILE__));
	define('UAMS_SHORTCAKES_PATH', plugin_dir_path(__FILE__));

	function UAMS_Shortcakes()
	{
	    return UAMS_Shortcakes::get_instance();
	}

	add_action('after_setup_theme', 'UAMS_Shortcakes');

	function UAMS_Shortcakes_add_editor_styles()
	{
		 add_editor_style( plugin_dir_url(__FILE__) . 'admin/css/admin.css' );
		//add_editor_style( 'https://cdn.ncsu.edu/brand-assets/bootstrap/css/bootstrap.css' );
	}
	add_action('admin_init', 'UAMS_Shortcakes_add_editor_styles');

	// Add editor JS to edit page
	function UAMS_Shortcakes_add_editor_js() {
	    wp_enqueue_script( 'uams_shortcodes_editor_js', plugin_dir_url(__FILE__) . 'admin/js/build/main.js' );
	}
	add_action( 'enqueue_shortcode_ui', 'UAMS_Shortcakes_add_editor_js' );
}


