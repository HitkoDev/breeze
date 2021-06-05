<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * Exclude pages from cache based on the specific given shortcodes.
 *
 * Class Exclude_Pages_By_Shortcode
 *
 * @since 1.1.8
 */
class Exclude_Pages_By_Shortcode {
    public function __construct() {
        add_action('save_post', [&$this, 'post_check_content_on_save'], 10, 3);
    }

    public function post_check_content_on_save($post_id = 0, $post = null, $update = false) {
        $content = trim($post->post_content);

        if ($post->post_type !== 'page') {
            return;
        }
        preg_match_all('/\[([^<>&\/\[\]\x00-\x20=]++)/ms', $content, $output_shortcodes);

        $saved_pages = get_option('breeze_exclude_url_pages', []);
        $saved_pages = array_map('absint', $saved_pages);
        $page_id = (int) $post->ID;
        $found = false;
        $action_taken = false;

        $shortcode_list = self::shortcode_exception_list_fixed();

        if (!empty($output_shortcodes)) {
            if (isset($output_shortcodes[1]) && !empty($output_shortcodes[1])) {
                $data = $output_shortcodes[1];
                $data = array_unique($data);

                foreach ($shortcode_list as $shortcode) {
                    $result = array_filter(
                        $data,
                        function ($item) use ($shortcode) {
                            $shortcode = str_replace('(.*)', '', $shortcode);
                            if (stripos($item, $shortcode) !== false) {
                                return true;
                            }

                            return false;
                        }
                    );

                    if (!empty($result)) {
                        $found = true;
                        break;
                    }
                }
            }

            if ($found === true) {
                if (!in_array($page_id, $saved_pages, true)) {
                    $saved_pages[] = $page_id;
                    update_option('breeze_exclude_url_pages', $saved_pages);
                    $action_taken = true;
                }
            } else {
                if (in_array($page_id, $saved_pages, true)) {
                    $saved_pages_modified = [];
                    foreach ($saved_pages as $index => $saved_page) {
                        if ($page_id !== $saved_page) {
                            $saved_pages_modified[] = $saved_page;
                        }
                    }
                    update_option('breeze_exclude_url_pages', $saved_pages_modified);
                    $action_taken = true;
                }
            }
        }

        if ($action_taken === true) {
            // reset the config file.
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }

            // import these file in front-end when required.
            if (!class_exists('Breeze_Ecommerce_Cache')) {
                //cache when ecommerce installed
                require_once BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php';
            }

            // import these file in front-end when required.
            if (!class_exists('Breeze_ConfigCache')) {
                //config to cache
                require_once BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php';
            }

            Breeze_ConfigCache::factory()->write_config_cache();
        }
    }

    public static function shortcode_exception_list_fixed() {
        $shortcode_exceptions = [
            'mycred_(.*)',
        ];

        return apply_filters('breeze_shortcode_page_exclude', $shortcode_exceptions);
    }
}

new Exclude_Pages_By_Shortcode();
