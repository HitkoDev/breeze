<?php
/**
 * @copyright 2017  Cloudways  https://www.cloudways.com
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
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

define( 'BREEZE_PLUGIN_FULL_PATH', dirname( __DIR__ ) . '/' );
require_once BREEZE_PLUGIN_FULL_PATH . 'inc/class-breeze-query-strings-rules.php';

/**
 * Get base path for the page cache directory.
 *
 * @param bool $is_network Whether to include the blog ID in the path on multisite.
 *
 * @param int $blog_id_requested Folder for specific blog ID.
 *
 * @return string
 */
function breeze_get_cache_base_path( $is_network = false, $blog_id_requested = 0 ) {

	if ( empty( $blog_id_requested ) ) {
		$blog_id_requested = isset( $GLOBALS['breeze_config']['blog_id'] ) ? $GLOBALS['breeze_config']['blog_id'] : 0;
	}

	if ( ! $is_network && is_multisite() ) {

		if ( empty( $blog_id_requested ) ) {
			global $blog_id;
			$path = rtrim( WP_CONTENT_DIR, '/\\' ) . '/cache/breeze/';
			if ( ! empty( $blog_id ) ) {
				$path .= abs( intval( $blog_id ) ) . DIRECTORY_SEPARATOR;
			}
		} else {
			$path  = rtrim( WP_CONTENT_DIR, '/\\' ) . '/cache/breeze/';
			$path .= abs( intval( $blog_id_requested ) ) . DIRECTORY_SEPARATOR;
		}
	} else {
		$path = rtrim( WP_CONTENT_DIR, '/\\' ) . '/cache/breeze/';
	}

	return $path;
}

/**
 * Get the total size of a directory (including subdirectories).
 *
 * @param string $dir
 * @param array $exclude
 *
 * @return int
 */
function breeze_get_directory_size( $dir, $exclude = array() ) {
	$size = 0;

	foreach ( glob( rtrim( $dir, '/' ) . '/*', GLOB_NOSORT ) as $path ) {
		if ( is_file( $path ) ) {
			if ( in_array( basename( $path ), $exclude ) ) {
				continue;
			}

			$size += filesize( $path );
		} else {
			$size += breeze_get_directory_size( $path, $exclude );
		}
	}

	return $size;
}

function breeze_current_user_type( $as_dir = true ) {
	$all_roles = array();
	if ( isset( $GLOBALS['breeze_config']['wp-user-roles'] ) ) {
		$all_roles = $GLOBALS['breeze_config']['wp-user-roles'];
	}

	foreach ( $all_roles as $user_role ) {
		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			if ( current_user_can( $user_role ) ) {
				return $user_role . ( true === $as_dir ? '/' : '' );
			}
		}
	}


	return '';
}


/**
 * Fetches all the current user roles in the wp install, including custom user roles.
 *
 * @return array
 * @since 1.2.5
 */
function breeze_all_wp_user_roles() {
	global $wp_roles;

	if ( empty( $wp_roles ) || is_wp_error( $wp_roles ) ) {
		return array();
	}

	$current_roles = array();
	$roles         = $wp_roles->roles;

	foreach ( $roles as $defined_user_role => $data ) {
		$current_roles[] = $defined_user_role;
	}

	return $current_roles;

}

function breeze_all_user_folders() {
	$all_roles = breeze_all_wp_user_roles();


	$roles = array(
		'',
		'administrator',
		'editor',
		'author',
		'contributor',
	);

	if ( ! empty( $all_roles ) ) {
		foreach ( $all_roles as $role ) {
			if ( ! in_array( $role, $roles, true ) ) {
				$roles[] = $role;
			}
		}
	}

	return $roles;
}

function breeze_is_feed( $url ) {

	$parse_result = parse_url( $url );

	if ( isset( $parse_result['query'] ) ) {
		if ( substr_count( $parse_result['query'], 'feed=' ) > 0 ) {
			return true;
		}
	}

	if ( isset( $parse_result['path'] ) ) {
		if ( substr_count( $parse_result['path'], '/feed' ) > 0 ) {
			return true;
		}
	}

	return false;

}


function breeze_treat_exceptions( $content ) {
	preg_match_all( '/<ins(.*)>/', $content, $matches );
	if ( ! empty( $matches ) && ( isset( $matches[0] ) && ! empty( $matches[0] ) ) ) {
		foreach ( $matches[0] as $html_tag ) {
			$decode  = html_entity_decode( $html_tag );
			$decode  = str_replace( '”', '', $decode );
			$content = str_replace( $html_tag, $decode, $content );
		}
	}

	return $content;

}

/**
 * Check for AMP based on URL.
 *
 * @param string $url Given url.
 *
 * @return bool
 */
function breeze_uri_amp_check( $url = '' ) {

	if (
		false !== strpos( $url, '/amp/' ) ||
		false !== strpos( $url, 'amp=1' ) ||
		false !== strpos( $url, '?amp' )
	) {
		return true;
	}

	return false;
}


if ( defined( 'AUTH_SALT' ) && ! empty( AUTH_SALT ) ) {
	define( 'BREEZE_WP_COOKIE_SALT', AUTH_SALT );
} else {
	define( 'BREEZE_WP_COOKIE_SALT', 'cQCDC6Z^R#FE*WpRHqfaWOfw!1baSb*NxeOP1B1^u9@X7x*%ah' );
}
define( 'BREEZE_WP_COOKIE', 'breeze_folder_name' );

add_action( 'set_auth_cookie', 'breeze_auth_cookie_set', 15, 6 );
function breeze_auth_cookie_set( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {

	if ( ! apply_filters( 'send_auth_cookies', true ) ) {
		return;
	}

	// get_userdata
	$current_user_roles = (array) get_userdata( $user_id )->roles;
	//$role               = reset( $current_user_roles );

	$all_roles = array();
	foreach ( $current_user_roles as $index => $one_role ) {
		$all_roles[] = sha1( BREEZE_WP_COOKIE_SALT . $one_role );
	}
	$role   = implode( '|&&&|', $all_roles );
	$secure = is_ssl();

	// Front-end cookie is secure when the auth cookie is secure and the site's home URL uses HTTPS.
	$secure_logged_in_cookie = $secure && 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME );
	$secure_logged_in_cookie = apply_filters( 'secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, $secure );

	setcookie( BREEZE_WP_COOKIE, $role, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
}

add_action( 'clear_auth_cookie', 'breeze_auth_cookie_clear' );

function breeze_auth_cookie_clear() {
	/** This filter is documented in wp-includes/pluggable.php */
	if ( ! apply_filters( 'send_auth_cookies', true ) ) {
		return;
	}
	setcookie( BREEZE_WP_COOKIE, ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH, COOKIE_DOMAIN );

}

add_action( 'init', 'breeze_auth_cookie_set_init', 5 );

function breeze_auth_cookie_set_init() {

	if ( is_user_logged_in() && ! isset( $_COOKIE[ BREEZE_WP_COOKIE ] ) || empty( BREEZE_WP_COOKIE ) ) {

		if ( ! apply_filters( 'send_auth_cookies', true ) ) {
			return;
		}

		$current_user       = wp_get_current_user();
		$current_user_roles = (array) $current_user->roles;
		//$role               = reset( $current_user_roles );
		$all_roles = array();
		foreach ( $current_user_roles as $index => $one_role ) {
			$all_roles[] = sha1( BREEZE_WP_COOKIE_SALT . $one_role );
		}
		$role   = implode( '|&&&|', $all_roles );
		$secure = is_ssl();

		// Front-end cookie is secure when the auth cookie is secure and the site's home URL uses HTTPS.
		$secure_logged_in_cookie = $secure && 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME );
		$secure_logged_in_cookie = apply_filters( 'secure_logged_in_cookie', $secure_logged_in_cookie, $current_user->ID, $secure );
		$expiration              = time() + apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, $current_user->ID, true );
		$expire                  = $expiration + ( 12 * HOUR_IN_SECONDS );

		setcookie( BREEZE_WP_COOKIE, $role, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
	}
}


function breeze_which_role_folder( $hash = '' ) {
	if ( empty( $hash ) ) {
		return false;
	}

	if ( isset( $GLOBALS['breeze_config'] ) && isset( $GLOBALS['breeze_config']['wp-user-roles'] ) ) {
		$cache_folders = $GLOBALS['breeze_config']['wp-user-roles'];
	} else {
		return '';
	}

	$hash_roles = explode( '|&&&|', $hash );

	$user_has_roles = array();

	if ( ! empty( $cache_folders ) ) {
		foreach ( $cache_folders as $folder ) {
			$coded = sha1( BREEZE_WP_COOKIE_SALT . $folder );

			if ( in_array( $coded, $hash_roles ) ) {
				$user_has_roles[] = $folder;
			}
		}
	}

	return $user_has_roles;
}
