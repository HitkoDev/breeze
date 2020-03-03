<?php
/**
 * Plugin Name: Breeze
 * Description: Breeze is a WordPress cache plugin with extensive options to speed up your website. All the options including Varnish Cache are compatible with Cloudways hosting.
 * Version: 1.1.4
 * Text Domain: breeze
 * Domain Path: /languages
 * Author: Cloudways
 * Author URI: https://www.cloudways.com
 * License: GPL2
 * Network: true
 */

/**
 *  @copyright 2017  Cloudways  https://www.cloudways.com
 *
 *  This plugin is inspired from WP Speed of Light by JoomUnited.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined('ABSPATH') || die('No direct script access allowed!');

if ( ! defined( 'BREEZE_PLUGIN_DIR' ) ) {
	define( 'BREEZE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'BREEZE_VERSION' ) ) {
	define( 'BREEZE_VERSION', '1.1.4' );
}
if ( ! defined( 'BREEZE_SITEURL' ) ) {
	define( 'BREEZE_SITEURL', get_site_url() );
}
if ( ! defined( 'BREEZE_MINIFICATION_CACHE' ) ) {
	define( 'BREEZE_MINIFICATION_CACHE', WP_CONTENT_DIR . '/cache/breeze-minification/' );
}
if ( ! defined( 'BREEZE_CACHEFILE_PREFIX' ) ) {
	define( 'BREEZE_CACHEFILE_PREFIX', 'breeze_' );
}
if ( ! defined( 'BREEZE_CACHE_CHILD_DIR' ) ) {
	define( 'BREEZE_CACHE_CHILD_DIR', '/cache/breeze-minification/' );
}
if ( ! defined( 'BREEZE_WP_CONTENT_NAME' ) ) {
	define( 'BREEZE_WP_CONTENT_NAME', '/' . wp_basename( WP_CONTENT_DIR ) );
}
if ( ! defined( 'BREEZE_BASENAME' ) ) {
	define( 'BREEZE_BASENAME', plugin_basename( __FILE__ ) );
}

define( 'BREEZE_CACHE_DELAY', true );
define( 'BREEZE_CACHE_NOGZIP', true );
define( 'BREEZE_ROOT_DIR', str_replace( BREEZE_WP_CONTENT_NAME, '', WP_CONTENT_DIR ) );


// Compatibility checks
require_once BREEZE_PLUGIN_DIR . 'inc/plugin-incompatibility/class-breeze-incompatibility-plugins.php';

// Helper functions.
require_once BREEZE_PLUGIN_DIR . 'inc/helpers.php';
require_once BREEZE_PLUGIN_DIR . 'inc/functions.php';

//action to purge cache
require_once( BREEZE_PLUGIN_DIR . 'inc/cache/purge-varnish.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/cache/purge-cache.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/cache/purge-per-time.php' );

// Activate plugin hook
register_activation_hook( __FILE__, array( 'Breeze_Admin', 'plugin_active_hook' ) );
//Deactivate plugin hook
register_deactivation_hook( __FILE__, array( 'Breeze_Admin', 'plugin_deactive_hook' ) );

require_once( BREEZE_PLUGIN_DIR . 'inc/breeze-admin.php' );

if ( is_admin() || 'cli' === php_sapi_name() ) {

	require_once( BREEZE_PLUGIN_DIR . 'inc/breeze-configuration.php' );
	//config to cache
	require_once( BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php' );

	//cache when ecommerce installed
	require_once( BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php' );
	add_action( 'init', function () {
		new Breeze_Ecommerce_Cache();
	}, 0 );

} else {
	$cdn_conf   = breeze_get_option( 'cdn_integration' );
	$basic_conf = breeze_get_option( 'basic_settings' );

	if ( ! empty( $cdn_conf['cdn-active'] ) || ! empty( $basic_conf['breeze-minify-js'] ) || ! empty( $basic_conf['breeze-minify-css'] ) || ! empty( $basic_conf['breeze-minify-html'] ) ) {
		// Call back ob start
		ob_start( 'breeze_ob_start_callback' );
	}
}

// Call back ob start - stack
function breeze_ob_start_callback( $buffer ) {
	$conf = breeze_get_option( 'cdn_integration' );
	// Get buffer from minify
	$buffer = apply_filters( 'breeze_minify_content_return', $buffer );

	if ( ! empty( $conf ) || ! empty( $conf['cdn-active'] ) ) {
		// Get buffer after remove query strings
		$buffer = apply_filters( 'breeze_cdn_content_return', $buffer );
	}

	// Return content
	return $buffer;
}

// Minify

require_once( BREEZE_PLUGIN_DIR . 'inc/minification/breeze-minify-main.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/minification/breeze-minification-cache.php' );
add_action( 'init', function () {
	new Breeze_Minify();

}, 0 );
// CDN Integration
if ( ! class_exists( 'Breeze_CDN_Integration' ) ) {
	require_once( BREEZE_PLUGIN_DIR . 'inc/cdn-integration/breeze-cdn-integration.php' );
	require_once( BREEZE_PLUGIN_DIR . 'inc/cdn-integration/breeze-cdn-rewrite.php' );
	add_action( 'init', function () {
		new Breeze_CDN_Integration();
	}, 0 );
}


/**
 * This function will update htaccess files after the plugin update is done.
 *
 * This function runs when WordPress completes its upgrade process.
 * It iterates through each plugin updated to see if ours is included.
 *
 * The plugin must be active while updating, otherwise this will do nothing.
 *
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/upgrader_process_complete
 * @since 1.1.3
 *
 * @param array $upgrader_object
 * @param array $options
 */
function breeze_after_plugin_update_done( $upgrader_object, $options ) {
	// If an update has taken place and the updated type is plugins and the plugins element exists.
	if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there
		foreach ( $options['plugins'] as $plugin ) {
			if ( $plugin == BREEZE_BASENAME ) {
				// Add a new option to inform the install that a new version was installed.
				add_option( 'breeze_new_update', 'yes', '', false );
			}
		}
	}
}

add_action( 'upgrader_process_complete', 'breeze_after_plugin_update_done', 10, 2 );

function breeze_check_for_new_version() {
	if ( ! empty( get_option( 'breeze_new_update', '' ) ) ) {
		if(class_exists('Breeze_Configuration') && method_exists('Breeze_Configuration','update_htaccess'))
		Breeze_Configuration::update_htaccess();
		delete_option( 'breeze_new_update' );
	}
}

add_action( 'init', 'breeze_check_for_new_version', 99 );

// @TODO: remove debug code.
if ( isset( $_GET['settings_debug'] ) ) {
	$settings = array(
		'basic_settings',
		'advanced_settings',
		'cdn_integration',
		'varnish_cache',
	);

	echo '<h1>Is multisite: ' . ( is_multisite() ? 'YES' : 'NO' ) . '</h1>';

	if ( is_multisite() ) {
		$inherit_option = get_option( 'breeze_inherit_settings' );
		$inherit        = true;

		if ( ! is_network_admin() && '0' === $inherit_option ) {
			$inherit = false;
		}

		echo '<h1>Using global settings: ' . ( $inherit ? 'YES' : 'NO' ) . '</h1>';
	}

	foreach ( $settings as $setting ) {
		echo '<h2>' . $setting . '</h2>';
		echo '<pre>';
		print_r( breeze_get_option( $setting ) );
		echo '</pre>';
	}

	echo '<h2>Gzip enabled: ' . ( getenv( 'BREEZE_GZIP_ON' ) ? 'YES' : 'NO' ) . '</h2>';
	echo '<h2>Browser cache enabled: ' . ( getenv( 'BREEZE_BROWSER_CACHE_ON' ) ? 'YES' : 'NO' ) . '</h2>';

	exit;
}
