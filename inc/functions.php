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
defined('ABSPATH') || exit('No direct script access allowed!');

/**
 * Get base path for the page cache directory.
 *
 * @param bool $is_network        Whether to include the blog ID in the path on multisite.
 * @param int  $blog_id_requested Folder for specific blog ID.
 *
 * @return string
 */
function breeze_get_cache_base_path($is_network = false, $blog_id_requested = 0) {
    if (empty($blog_id_requested)) {
        $blog_id_requested = isset($GLOBALS['breeze_config']['blog_id']) ? $GLOBALS['breeze_config']['blog_id'] : 0;
    }

    if (!$is_network && is_multisite()) {
        if (empty($blog_id_requested)) {
            global $blog_id;
            $path = rtrim(WP_CONTENT_DIR, '/\\') . '/cache/breeze/';
            if (!empty($blog_id)) {
                $path .= abs(intval($blog_id)) . DIRECTORY_SEPARATOR;
            }
        } else {
            $path = rtrim(WP_CONTENT_DIR, '/\\') . '/cache/breeze/';
            $path .= abs(intval($blog_id_requested)) . DIRECTORY_SEPARATOR;
        }
    } else {
        $path = rtrim(WP_CONTENT_DIR, '/\\') . '/cache/breeze/';
    }

    return $path;
}

/**
 * Get the total size of a directory (including subdirectories).
 *
 * @param string $dir
 * @param array  $exclude
 *
 * @return int
 */
function breeze_get_directory_size($dir, $exclude = []) {
    $size = 0;

    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $path) {
        if (is_file($path)) {
            if (in_array(basename($path), $exclude)) {
                continue;
            }

            $size += filesize($path);
        } else {
            $size += breeze_get_directory_size($path, $exclude);
        }
    }

    return $size;
}

function breeze_current_user_type($as_dir = true) {
    if (function_exists('is_user_logged_in') && is_user_logged_in()) {
        if (current_user_can('administrator')) {
            return 'administrator' . ($as_dir === true ? '/' : '');
        }
        if (current_user_can('editor')) {
            return 'editor' . ($as_dir === true ? '/' : '');
        }
        if (current_user_can('author')) {
            return 'author' . ($as_dir === true ? '/' : '');
        }
        if (current_user_can('contributor')) {
            return 'contributor' . ($as_dir === true ? '/' : '');
        }
    }

    return '';
}

function breeze_all_user_folders() {
    return [
        '',
        'administrator',
        'editor',
        'author',
        'contributor',
    ];
}

function breeze_is_feed($url) {
    $parse_result = parse_url($url);

    if (isset($parse_result['query'])) {
        if (substr_count($parse_result['query'], 'feed=') > 0) {
            return true;
        }
    }

    if (isset($parse_result['path'])) {
        if (substr_count($parse_result['path'], '/feed') > 0) {
            return true;
        }
    }

    return false;
}
