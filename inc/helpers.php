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

/**
 * Retrieve site options accounting for settings inheritance.
 *
 * @param string $option_name
 * @param bool $is_local
 *
 * @return array
 */
function breeze_get_option( $option_name, $is_local = false ) {
	$inherit = true;

	global $breeze_network_subsite_settings;

	if ( is_network_admin() && ! $breeze_network_subsite_settings ) {
		$is_local = false;
	} elseif ( ! breeze_does_inherit_settings() ) {
		$inherit = false;
	}

	if ( ! is_multisite() || $is_local || ! $inherit ) {
		$option = get_option( 'breeze_' . $option_name );
	} else {
		$option = get_site_option( 'breeze_' . $option_name );
	}

	if ( empty( $option ) || ! is_array( $option ) ) {
		$option = array();
	}

	return $option;
}

/**
 * Update site options accounting for multisite.
 *
 * @param string $option_name
 * @param mixed $value
 * @param bool $is_local
 */
function breeze_update_option( $option_name, $value, $is_local = false ) {
	if ( is_network_admin() ) {
		$is_local = false;
	}

	if ( ! is_multisite() || $is_local ) {
		update_option( 'breeze_' . $option_name, $value );
	} else {
		update_site_option( 'breeze_' . $option_name, $value );
	}
}

/**
 * Check whether current site should inherit network-level settings.
 *
 * @return bool
 */
function breeze_does_inherit_settings() {
	global $breeze_network_subsite_settings;

	if ( ! is_multisite() || ( ! $breeze_network_subsite_settings && is_network_admin() ) ) {
		return false;
	}

	$inherit_option = get_option( 'breeze_inherit_settings' );

	return '0' !== $inherit_option;
}

/**
 * Check if plugin is activated network-wide in a multisite environment.
 *
 * @return bool
 */
function breeze_is_active_for_network() {
	return is_multisite() && is_plugin_active_for_network( 'breeze/breeze.php' );
}

function breeze_is_supported( $check ) {
	switch ( $check ) {
		case 'conditional_htaccess':
			$return = isset( $_SERVER['SERVER_SOFTWARE'] ) && stripos( $_SERVER['SERVER_SOFTWARE'], 'Apache/2.4' ) !== false;
			break;
	}

	return $return;
}

/**
 * If an array provided, the function will check all
 * array items to see if all of them are valid URLs.
 *
 * @param array $url_list
 * @param string $extension
 *
 * @since 1.1.0
 *
 * @return bool
 */
function breeze_validate_urls( $url_list = array() ) {
	if ( ! is_array( $url_list ) ) {
		return false;
	}

	$is_valid = true;
	foreach ( $url_list as $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$is_valid = false;
			if ( false === $is_valid ) {
				$is_valid = breeze_validate_url_via_regexp( $url );
			}
		}

		if ( false === $is_valid ) {
			break;
		}
	}

	return $is_valid;

}

function breeze_validate_the_right_extension( $url_list = array(), $extension = 'css' ) {
	if ( ! is_array( $url_list ) ) {
		return false;
	}

	$is_valid = true;
	foreach ( $url_list as $url ) {

		$is_regexp = breeze_string_contains_exclude_regexp( $url );

		if ( false === $is_regexp ) {
			$is_valid = breeze_validate_exclude_field_by_extension( $url, $extension );
		} else {
			$file_extension = breeze_get_file_extension_from_url( $url );

			if ( false !== $file_extension && strtolower( $extension ) !== $file_extension ) {
				$is_valid = false;
			}
		}

		if ( false === $is_valid ) {
			break;
		}
	}

	return $is_valid;
}

/**
 * Returns the extension for given file from url.
 *
 * @param string $url_given
 *
 * @return bool
 */
function breeze_get_file_extension_from_url( $url_given = '' ) {
	if ( empty( $url_given ) ) {
		return false;
	}

	$file_path = wp_parse_url( $url_given, PHP_URL_PATH );
	if ( ! empty( $file_path ) ) {
		$file_name = wp_basename( $file_path );
		if ( ! empty( $file_name ) ) {
			$bits = explode( '.', $file_name );
			if ( ! empty( $bits ) ) {
				$extension_id = count( $bits ) - 1;
				$extension    = strtolower( $bits[ $extension_id ] );
				$extension    = preg_replace( '/\s+/', ' ', $extension );
				if ( '*)' === $extension ) { // Exception when (.*) is the last statement instead of ending with an extension
					return false;
				}

				return $extension;
			}
		}
	}

	return false;
}

/**
 * Will search for given string in array values
 * if found, will result in an array with all entries found
 * if not found, an empty array will be resulted.
 *
 * @since 1.1.0
 *
 * @param string $needle
 * @param array $haystack
 *
 * @return array
 */
function breeze_is_string_in_array_values( $needle = '', $haystack = array() ) {
	if ( empty( $needle ) || empty( $haystack ) ) {
		return array();
	}
	$needle             = trim( $needle );
	$is_string_in_array = array_filter(
		$haystack,
		function ( $var ) use ( $needle ) {
			#return false;
			if ( breeze_string_contains_exclude_regexp( $var ) ) {
				return breeze_file_match_pattern( $needle, $var );
			} else {
				return strpos( $var, $needle ) !== false;
			}

		}
	);

	return $is_string_in_array;
}

/**
 * Will return true for Google fonts and other type of CDN link
 * that are missing the Scheme from the url
 *
 *
 * @param string $url_to_be_checked
 *
 * @return bool
 */
function breeze_validate_url_via_regexp( $url_to_be_checked = '' ) {
	if ( empty( $url_to_be_checked ) ) {
		return false;
	}
	$regex = '((http:|https:?)?\/\/)?([a-z0-9+!*(),;?&=.-]+(:[a-z0-9+!*(),;?&=.-]+)?@)?([a-z0-9\-\.]*)\.(([a-z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))(:[0-9]{2,5})?(\/([a-z0-9+%-]\.?)+)*\/?(\?[a-z+&$_.-][a-z0-9;:@&%=+/.-/,/:]*)?(#[a-z_.-][a-z0-9+$%_.-]*)?';

	preg_match( "~^$regex$~i", $url_to_be_checked, $matches_found );

	if ( empty( $matches_found ) ) {
		return false;
	}

	return true;
}


/**
 * Used in Breeze settings to validate if the URL corresponds to the
 * added input/textarea
 * Exclude CSS must contain only .css files
 * Exclude JS must contain only .js files
 *
 * @param $file_url
 * @param string $validate
 *
 * @return bool
 */
function breeze_validate_exclude_field_by_extension( $file_url, $validate = 'css' ) {
	if ( empty( $file_url ) ) {
		return true;
	}
	if ( empty( $validate ) ) {
		return false;
	}

	$valid      = true;
	$file_path  = wp_parse_url( $file_url, PHP_URL_PATH );
	$preg_match = preg_match( '#\.' . $validate . '$#', $file_path );
	if ( empty( $preg_match ) ) {
		$valid = false;
	}

	return $valid;

}


/**
 * Function used to determine if the excluded URL contains regexp
 *
 * @param $file_url
 * @param string $validate
 *
 * @return bool
 */
function breeze_string_contains_exclude_regexp( $file_url, $validate = '(.*)' ) {
	if ( empty( $file_url ) ) {
		return false;
	}
	if ( empty( $validate ) ) {
		return false;
	}

	$valid = false;

	if ( strpos( $file_url, $validate ) !== false ) {
		$valid = true; // 0 or false
	}

	return $valid;
}

/**
 * Method will prepare the URLs escaped for preg_match
 * Will return the file_url matches the pattern.
 * empty array for false,
 * aray with data for true.
 *
 * @param $file_url
 * @param $pattern
 *
 * @return false|int
 */
function breeze_file_match_pattern( $file_url, $pattern ) {
	$remove_pattern   = str_replace( '(.*)', 'REG_EXP_ALL', $pattern );
	$prepared_pattern = preg_quote( $remove_pattern, '/' );
	$pattern          = str_replace( 'REG_EXP_ALL', '(.*)', $prepared_pattern );
	$result           = preg_match( '/' . $pattern . '/', $file_url );

	return $result;
}


/**
 * Will return true/false if the cache headers exist and
 * have values HIT or MISS.
 * HIT = Varnish is enabled and age is cached
 * MISS = Varnish is disabled or the cache has been purged.
 * This method will request only the current url homepage headers
 * and if the first time is a MISS, it will try again.
 *
 * @param int $retry
 *
 * @return bool
 */
function is_varnish_cache_started( $retry = 1 ) {
	// Making sure the request is only for HEADER info without getting the content from the page
	$context_options = array(
		'http' => array(
			'method'          => 'HEAD',
			'follow_location' => 1,
		),
		'ssl'  => array(
			'verify_peer' => false
		),
	);
	$context         = stream_context_create( $context_options );

	$url_ping = trim( home_url() . '?breeze_check_cache_available=' . time() );
	$headers  = get_headers( $url_ping, 1, $context );

	if ( empty( $headers ) ) {
		return false;
	}

	$headers = array_change_key_case( $headers, CASE_LOWER );

	if ( ! isset( $headers['x-cache'] ) ) {
		if ( 1 === $retry ) {
			$retry ++;

			return is_varnish_cache_started( $retry );
		}

		return false;
	} else {
		$cache_header = strtolower( trim( $headers['x-cache'] ) );

		// After the cache is cleared, the first time the headers will say that the cache is not used
		// After the first header requests, the cache headers are formed.
		// Checking the second time will give better results.
		if ( 1 === $retry ) {
			if ( 'hit' === $cache_header ) {
				return true;
			} else {
				$retry ++;

				return is_varnish_cache_started( $retry );
			}
		} else {

			if ( 'hit' === $cache_header ) {
				return true;
			}

			return false;
		}
	}
}
