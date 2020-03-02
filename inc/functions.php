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
 * Get base path for the page cache directory.
 *
 * @param bool $is_network Whether to include the blog ID in the path on multisite.
 *
 * @return string
 */
function breeze_get_cache_base_path( $is_network = false ) {

	if ( ! $is_network && is_multisite() ) {
		global $blog_id;
		$path = rtrim( WP_CONTENT_DIR, '/\\' ) . '/cache/breeze/';

		if ( ! empty( $blog_id ) ) {
			$path .= abs( intval( $blog_id ) ) . DIRECTORY_SEPARATOR;
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

	if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
		if ( current_user_can( 'administrator' ) ) {
			return 'administrator' . ( true === $as_dir ? '/' : '' );
		} elseif ( current_user_can( 'editor' ) ) {
			return 'editor' . ( true === $as_dir ? '/' : '' );
		} elseif ( current_user_can( 'author' ) ) {
			return 'author' . ( true === $as_dir ? '/' : '' );
		} elseif ( current_user_can( 'contributor' ) ) {
			return 'contributor' . ( true === $as_dir ? '/' : '' );
		}
	}

	return '';
}

function breeze_all_user_folders() {
	return array(
		'',
		'administrator',
		'editor',
		'author',
		'contributor',
	);
}
