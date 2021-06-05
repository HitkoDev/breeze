<?php
/*
 *  Based on some work of https://github.com/tlovett1/simple-cache/blob/master/inc/dropins/file-based-page-cache.php
 */
defined('ABSPATH') || exit;

// Load helper functions.
require_once dirname(__DIR__) . '/functions.php';

// Load lazy Load class.
require_once dirname(__DIR__) . '/class-breeze-lazy-load.php';

// Include and instantiate the class.
include_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
$detect = new Detection\MobileDetect();

// Don't cache robots.txt or htacesss
if (strpos($_SERVER['REQUEST_URI'], 'robots.txt') !== false || strpos($_SERVER['REQUEST_URI'], '.htaccess') !== false) {
    return;
}

if (
    strpos($_SERVER['REQUEST_URI'], 'breeze-minification') !== false
    || strpos($_SERVER['REQUEST_URI'], 'favicon.ico') !== false
) {
    return;
}

// Don't cache non-GET requests
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    return;
}

$file_extension = $_SERVER['REQUEST_URI'];
$file_extension = preg_replace('#^(.*?)\?.*$#', '$1', $file_extension);
$file_extension = trim(preg_replace('#^.*\.(.*)$#', '$1', $file_extension));

// Don't cache disallowed extensions. Prevents wp-cron.php, xmlrpc.php, etc.
if (!preg_match('#index\.php$#i', $_SERVER['REQUEST_URI']) && in_array($file_extension, ['php', 'xml', 'xsl'])) {
    return;
}

$filename_guest_suffix = '';
$url_path = breeze_get_url_path();
$user_logged = false;

if (substr_count($url_path, '?') > 0) {
    $filename = $url_path . '&guest';
    $filename_guest_suffix = '&guest';
} else {
    $filename = $url_path . '?guest';
    $filename_guest_suffix = '?guest';
}

// Don't cache
if (!empty($_COOKIE)) {
    $wp_cookies = ['wordpressuser_', 'wordpresspass_', 'wordpress_sec_', 'wordpress_logged_in_'];

    foreach ($_COOKIE as $key => $value) {
        // Logged in!
        if (strpos($key, 'wordpress_logged_in_') !== false) {
            $user_logged = true;
        }
    }

    if ($user_logged) {
        foreach ($_COOKIE as $k => $v) {
            if (strpos($k, 'wordpress_logged_in_') !== false) {
                $nameuser = substr($v, 0, strpos($v, '|'));
                if (substr_count($url_path, '?') > 0) {
                    $filename = $url_path . '&' . strtolower($nameuser);
                } else {
                    $filename = $url_path . '?' . strtolower($nameuser);
                }
            }
        }
    }

    if (!empty($_COOKIE['breeze_commented_posts'])) {
        foreach ($_COOKIE['breeze_commented_posts'] as $path) {
            if (rtrim($path, '/') === rtrim($_SERVER['REQUEST_URI'], '/')) {
                // User commented on this post
                return;
            }
        }
    }
}

//check disable cache for page
$domain = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
//decode url with russian language
$current_url = $domain . rawurldecode($_SERVER['REQUEST_URI']);
$opts_config = $GLOBALS['breeze_config'];
$check_exclude = check_exclude_page($opts_config, $current_url);

//load cache
if (!$check_exclude) {
    $devices = $opts_config['cache_options'];
    $X1 = '';
    // Detect devices
    if ($detect->isMobile() && !$detect->isTablet()) {
        //        The first X will be D for Desktop cache
        //                            M for Mobile cache
        //                            T for Tablet cache
        if ((int) $devices['breeze-mobile-cache'] == 1) {
            $X1 = 'D';
            $filename .= '_breeze_cache_desktop';
        }
        if ((int) $devices['breeze-mobile-cache'] == 2) {
            $X1 = 'M';
            $filename .= '_breeze_cache_mobile';
        }
    } else {
        if ((int) $devices['breeze-desktop-cache'] == 1) {
            $X1 = 'D';
            $filename .= '_breeze_cache_desktop';
        }
    }

    breeze_serve_cache($filename, $url_path, $X1, $devices);
    ob_start('breeze_cache');
} else {
    header('Cache-Control: no-cache');
}

/**
 * Cache output before it goes to the browser
 *
 * @param string $buffer
 * @param int    $flags
 *
 * @return string
 *
 * @since  1.0
 */
function breeze_cache($buffer, $flags) {
    // No cache for pages without 200 response status
    if (http_response_code() !== 200) {
        return $buffer;
    }

    include_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
    $detect = new Detection\MobileDetect();
    //not cache per administrator if option disable optimization for admin users clicked
    if (!empty($GLOBALS['breeze_config']) && (int) $GLOBALS['breeze_config']['disable_per_adminuser']) {
        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if (in_array('administrator', $current_user->roles)) {
                return $buffer;
            }
        }
    }

    if (strlen($buffer) < 255) {
        return $buffer;
    }

    // Don't cache search, 404, or password protected
    if (is_404() || is_search() || post_password_required()) {
        return $buffer;
    }
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
    }
    $url_path = breeze_get_url_path();

    $blog_id_requested = isset($GLOBALS['breeze_config']['blog_id']) ? $GLOBALS['breeze_config']['blog_id'] : 0;
    $cache_base_path = breeze_get_cache_base_path(false, $blog_id_requested);

    $path = $cache_base_path . md5($url_path);

    // Make sure we can read/write files and that proper folders exist
    if (!wp_mkdir_p($path)) {
        // Can not cache!
        return $buffer;
    }
    $path .= '/';

    $modified_time = time(); // Make sure modified time is consistent

    if (preg_match('#</html>#i', $buffer)) {
        $buffer .= "\n<!-- Cache served by breeze CACHE - Last modified: " . gmdate('D, d M Y H:i:s', $modified_time) . " GMT -->\n";
    }
    $headers = [
        [
            'name' => 'Content-Length',
            'value' => strlen($buffer),
        ],
        [
            'name' => 'Content-Type',
            'value' => 'text/html; charset=utf-8',
        ],
        [
            'name' => 'Last-Modified',
            'value' => gmdate('D, d M Y H:i:s', $modified_time) . ' GMT',
        ],
    ];

    if (!isset($_SERVER['HTTP_X_VARNISH'])) {
        $headers = array_merge(
            $headers,
            [
                [
                    'name' => 'Expires',
                    'value' => 'Wed, 17 Aug 2005 00:00:00 GMT',
                ],
                [
                    'name' => 'Cache-Control',
                    'value' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                ],
                [
                    'name' => 'Pragma',
                    'value' => 'no-cache',
                ],
            ]
        );
    }

    // Lazy load implementation
    if (class_exists('Breeze_Lazy_Load')) {
        $is_lazy_load_enabled = filter_var($GLOBALS['breeze_config']['enabled-lazy-load'], FILTER_VALIDATE_BOOLEAN);
        $is_lazy_load_native = filter_var($GLOBALS['breeze_config']['use-lazy-load-native'], FILTER_VALIDATE_BOOLEAN);

        $lazy_load = new Breeze_Lazy_Load($buffer, $is_lazy_load_enabled, $is_lazy_load_native);
        $buffer = $lazy_load->apply_lazy_load_feature();
    }

    if (isset($GLOBALS['breeze_config']['cache_options']['breeze-cross-origin']) && filter_var($GLOBALS['breeze_config']['cache_options']['breeze-cross-origin'], FILTER_VALIDATE_BOOLEAN)) {
        // Extract all <a> tags from the page.
        preg_match_all('/(?i)<a ([^>]+)>(.+?)<\/a>/', $buffer, $matches);

        $home_url = $GLOBALS['breeze_config']['homepage'];
        $home_url = ltrim($home_url, 'https:');

        if (!empty($matches) && isset($matches[0]) && !empty($matches[0])) {
            $current_links = $matches[0];

            foreach ($current_links as $index => $html_a_tag) {
                // If the A tag qualifies.
                if (
                    strpos($html_a_tag, $home_url) === false
                    && strpos($html_a_tag, 'target') !== false
                    && strpos($html_a_tag, '_blank') !== false
                ) {
                    $anchor_attributed = new SimpleXMLElement($html_a_tag);
                    // Only apply on valid URLS.
                    if (
                        !empty($anchor_attributed)
                        && isset($anchor_attributed['href'])
                        && filter_var($anchor_attributed['href'], FILTER_VALIDATE_URL)
                    ) {
                        // Apply noopener noreferrer on the A tag
                        $replacement_rel = 'noopener noreferrer';
                        $html_a_tag_replace = $html_a_tag;
                        if (isset($anchor_attributed['rel']) && !empty($anchor_attributed['rel'])) {
                            if (strpos($anchor_attributed['rel'], 'noopener') === false && strpos($anchor_attributed['rel'], 'noreferrer') === false) {
                                $replacement_rel = 'noopener noreferrer';
                            } elseif (strpos($anchor_attributed['rel'], 'noopener') === false) {
                                $replacement_rel = 'noopener';
                            } elseif (strpos($anchor_attributed['rel'], 'noreferrer') === false) {
                                $replacement_rel = 'noreferrer';
                            }
                            $replacement_rel .= ' ' . $anchor_attributed['rel'];
                            $html_a_tag_replace = preg_replace('/(<[^>]+) rel=".*?"/i', '$1', $html_a_tag);
                        }
                        $html_a_tag_rel = preg_replace('/(<a\b[^><]*)>/i', '$1 rel="' . $replacement_rel . '">', $html_a_tag_replace);
                        $buffer = str_replace($html_a_tag, $html_a_tag_rel, $buffer);
                    }
                }
            }
        }
    }

    $data = serialize(
        [
            'body' => $buffer,
            'headers' => $headers,
        ]
    );
    //cache per users
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        if ($current_user->user_login) {
            if (substr_count($url_path, '?') > 0) {
                $url_path .= '&' . $current_user->user_login;
            } else {
                $url_path .= '?' . $current_user->user_login;
            }
            //$url_path .= $current_user->user_login;
        }
    } else {
        global $filename_guest_suffix;
        $url_path .= $filename_guest_suffix;
    }
    $devices = $GLOBALS['breeze_config']['cache_options'];
    // Detect devices
    if ($detect->isMobile() && !$detect->isTablet()) {
        if ($devices['breeze-mobile-cache'] == 1) {
            $X1 = 'D';
            $url_path .= '_breeze_cache_desktop';
        }
        if ($devices['breeze-mobile-cache'] == 2) {
            $X1 = 'M';
            $url_path .= '_breeze_cache_mobile';
        }
    } else {
        if ($devices['breeze-desktop-cache'] == 1) {
            $X1 = 'D';
            $url_path .= '_breeze_cache_desktop';
        }
    }

    if (strpos($url_path, '_breeze_cache_') !== false) {
        if (!empty($GLOBALS['breeze_config']['cache_options']['breeze-gzip-compression']) && function_exists('gzencode')) {
            $wp_filesystem->put_contents($path . md5($url_path . '/index.gzip.html') . '.php', $data);
            $wp_filesystem->touch($path . md5($url_path . '/index.gzip.html') . '.php', $modified_time);
        } else {
            $wp_filesystem->put_contents($path . md5($url_path . '/index.html') . '.php', $data);
            $wp_filesystem->touch($path . md5($url_path . '/index.html') . '.php', $modified_time);
        }
    } else {
        return $buffer;
    }

    //set cache provider header if not exists cache file
    header('Cache-Provider:CLOUDWAYS-CACHE-' . $X1 . 'C');

    // Do not send this header in case we are behind a varnish proxy
    if (!isset($_SERVER['HTTP_X_VARNISH'])) {
        header('Cache-Control: no-cache'); // Check back every time to see if re-download is necessary
    }

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified_time) . ' GMT');

    if (function_exists('ob_gzhandler') && !empty($GLOBALS['breeze_config']['cache_options']['breeze-gzip-compression'])) {
        $ini_output_compression = ini_get('zlib.output_compression');
        $array_values = ['1', 'On', 'on'];
        if (in_array($ini_output_compression, $array_values)) {
            return $buffer;
        }
        return ob_gzhandler($buffer, $flags);
    }
    return $buffer;
}

/**
 * Get URL path for caching
 *
 * @return string
 *
 * @since  1.0
 */
function breeze_get_url_path() {
    $host = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '';
    $domain = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://');

    return $domain . rtrim($host, '/') . $_SERVER['REQUEST_URI'];
}

/**
 * Optionally serve cache and exit
 *
 * @since 1.0
 *
 * @param mixed $filename
 * @param mixed $url_path
 * @param mixed $X1
 * @param mixed $opts
 */
function breeze_serve_cache($filename, $url_path, $X1, $opts) {
    if (strpos($filename, '_breeze_cache_') === false) {
        return;
    }

    if (function_exists('gzencode') && !empty($GLOBALS['breeze_config']['cache_options']['breeze-gzip-compression'])) {
        $file_name = md5($filename . '/index.gzip.html') . '.php';
    } else {
        $file_name = md5($filename . '/index.html') . '.php';
    }

    $blog_id_requested = isset($GLOBALS['breeze_config']['blog_id']) ? $GLOBALS['breeze_config']['blog_id'] : 0;
    $path = breeze_get_cache_base_path(false, $blog_id_requested) . md5($url_path) . '/' . $file_name;

    $modified_time = 0;
    if (file_exists($path)) {
        $modified_time = (int) @filemtime($path);
    }

    if (!empty($opts['breeze-browser-cache']) && !empty($modified_time) && !empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $modified_time) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
        exit;
    }

    if (@file_exists($path)) {
        $cacheFile = file_get_contents($path);

        if ($cacheFile != false) {
            $datas = unserialize($cacheFile);
            foreach ($datas['headers'] as $data) {
                header($data['name'] . ': ' . $data['value']);
            }
            //set cache provider header
            header('Cache-Provider:CLOUDWAYS-CACHE-' . $X1 . 'E');

            $client_support_gzip = true;

            //check gzip request from client
            if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') === false)) {
                $client_support_gzip = false;
            }

            if ($client_support_gzip && function_exists('gzdecode') && !empty($GLOBALS['breeze_config']['cache_options']['breeze-gzip-compression'])) {
                //if file is zip

                $content = gzencode($datas['body'], 9);
                header('Content-Encoding: gzip');
                header('cache-control: must-revalidate');
                header('Content-Length: ' . strlen($content));
                header('Vary: Accept-Encoding');
                echo $content;
            } else {
                header('Content-Length: ' . strlen($datas['body']));
                //render page cache
                echo $datas['body'];
            }
            exit;
        }
    }
}

function check_exclude_page($opts_config, $current_url) {
    $is_feed = breeze_is_feed($current_url);

    if ($is_feed === true) {
        return true;
    }

    //check disable cache for page
    if (!empty($opts_config['exclude_url'])) {
        $is_exclude = exec_breeze_check_for_exclude_values($current_url, $opts_config['exclude_url']);
        if (!empty($is_exclude)) {
            return true;
        }

        foreach ($opts_config['exclude_url'] as $v) {
            // Clear blank character
            $v = trim($v);
            if (preg_match('/(\&?\/?\(\.?\*\)|\/\*|\*)$/', $v, $matches)) {
                // End of rules is *, /*, [&][/](*) , [&][/](.*)
                $pattent = substr($v, 0, strpos($v, $matches[0]));
                if ($v[0] == '/') {
                    // A path of exclude url with regex
                    if ((@preg_match('@' . $pattent . '@', $current_url, $matches) > 0)) {
                        return true;
                    }
                } else {
                    // Full exclude url with regex
                    if (strpos($current_url, $pattent) !== false) {
                        return true;
                    }
                }
            } else {
                if ($v[0] == '/') {
                    // A path of exclude
                    if ((@preg_match('@' . $v . '@', $current_url, $matches) > 0)) {
                        return true;
                    }
                } else { // Whole path
                    if ($v == $current_url) {
                        return true;
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Used to check for regexp exclude pages
 *
 * @param string $needle
 * @param array  $haystack
 *
 * @return array
 *
 * @since 1.1.7
 */
function exec_breeze_check_for_exclude_values($needle = '', $haystack = []) {
    if (empty($needle) || empty($haystack)) {
        return [];
    }
    $needle = trim($needle);
    return array_filter(
        $haystack,
        function ($var) use ($needle) {
            if (exec_breeze_string_contains_exclude_regexp($var)) {
                return exec_breeze_file_match_pattern($needle, $var);
            }
            return false;
        }
    );
}

/**
 * Function used to determine if the excluded URL contains regexp
 *
 * @param $file_url
 * @param string $validate
 *
 * @return bool
 */
function exec_breeze_string_contains_exclude_regexp($file_url, $validate = '(.*)') {
    if (empty($file_url)) {
        return false;
    }
    if (empty($validate)) {
        return false;
    }

    $valid = false;

    if (substr_count($file_url, $validate) !== 0) {
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
function exec_breeze_file_match_pattern($file_url, $pattern) {
    $remove_pattern = str_replace('(.*)', 'REG_EXP_ALL', $pattern);
    $prepared_pattern = preg_quote($remove_pattern, '/');
    $pattern = str_replace('REG_EXP_ALL', '(.*)', $prepared_pattern);
    return preg_match('/' . $pattern . '/', $file_url);
}
