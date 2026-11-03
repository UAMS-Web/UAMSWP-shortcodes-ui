<?php

/*
 * Plugin Name: UAMSWP Shortcodes UI
 * Version: 0.5
 * Plugin URI:
 * Description: Useful shortcodes for UAMS websites, which can be added using the Shortcake shortcode UI.
 * Author: Todd McKee, MEd, Original: OIT Design, Brian DeConinck, Chris Deaton
 * Author URI: https://www.uams.edu/
 * Requires at least: 4.6
 * Requires PHP: 8.2
 * Tested up to: 4.9.9
 *
 * @package WordPress
 * @since 0.3.0
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    exit;
}

if (! function_exists('is_plugin_active')) {
    require_once ABSPATH.'/wp-admin/includes/plugin.php';
}

// This plugin requires PHP 8.2 or greater.
if (version_compare(PHP_VERSION, '8.2', '<')) {
    add_action('admin_notices', function (): void {
        echo '<div class="error"><p>'.__('UAMSWP Shortcode UI requires PHP 8.2 to function properly. Please upgrade PHP or deactivate the plugin.', 'uamswp-shortcode-ui').'</p></div>';
    });

    return;
}

// This plugin requires PHP 8.2 or greater.
if (! is_plugin_active('shortcode-ui/shortcode-ui.php')) {
    add_action('admin_notices', function (): void {
        echo '<div class="error"><p>'.__('UAMSWP Shortcode UI requires <a href="https://wordpress.org/plugins/shortcode-ui/">Shortcake (Shortcode UI)</a> plugin to be active!', 'uamswp-shortcode-ui').'</p></div>';
    });

    return;
}

require_once __DIR__.'/inc/class-uams-shortcodes.php';
define('UAMS_SHORTCAKES_VERSION', '0.4');
define('UAMS_SHORTCAKES_URL_ROOT', plugin_dir_url(__FILE__));
define('UAMS_SHORTCAKES_PATH', plugin_dir_path(__FILE__));
function UAMS_Shortcakes(): UAMS_Shortcakes
{
    return UAMS_Shortcakes::get_instance();
}

add_action('after_setup_theme', 'UAMS_Shortcakes');
function UAMS_Shortcakes_add_editor_styles(): void
{
    add_editor_style(plugin_dir_url(__FILE__).'admin/css/admin.css');
    // add_editor_style( 'https://cdn.ncsu.edu/brand-assets/bootstrap/css/bootstrap.css' );
}

add_action('admin_init', 'UAMS_Shortcakes_add_editor_styles');
// Add editor JS to edit page
function UAMS_Shortcakes_add_editor_js(): void
{
    wp_enqueue_script('uams_shortcodes_editor_js', plugin_dir_url(__FILE__).'admin/js/build/main.js');
}

add_action('enqueue_shortcode_ui', 'UAMS_Shortcakes_add_editor_js');
