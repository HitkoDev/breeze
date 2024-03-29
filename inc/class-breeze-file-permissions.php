<?php

class Breeze_File_Permissions {
    /**
     * @var null Hold the class instance.
     */
    private static $instance = null;

    /**
     * @var array All the errors found.
     */
    private static $errors = [];

    public function __construct() {
        add_action('admin_notices', [&$this, 'display_the_errors']);
        add_action('network_admin_notices', [&$this, 'display_the_errors']);
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Breeze_File_Permissions();
        }

        return self::$instance;
    }

    public static function append_permission_error($message = '') {
        if (!empty($message)) {
            self::$errors[] = $message;
        }
    }

    public function check_specific_files_folders() {
        $cache_specific_folders = breeze_all_user_folders();
        $assets_folders = [
            'css',
            'js',
            '',
        ];
        $wp_content_dir = trailingslashit(WP_CONTENT_DIR);

        /**
         * Check global cache folders.
         */

        // Advanced cache file.
        $file = $wp_content_dir . 'advanced-cache.php';

        if (!is_writable($file)) {
            self::append_permission_error($file . __(' file is not writable.', 'breeze'));
        }

        $folder = $wp_content_dir . 'breeze-config/';

        if (is_dir($folder) && !is_writable($folder)) {
            self::append_permission_error($folder . __('  folder is not writable.', 'breeze'));
        }

        $folder = $wp_content_dir . 'cache/';
        if (is_dir($folder) && !is_writable($folder)) {
            self::append_permission_error($folder . __(' folder is not writable.', 'breeze'));
        }

        $folder = $wp_content_dir . 'cache/breeze/';
        if (is_dir($folder) && !is_writable($folder)) {
            self::append_permission_error($folder . __(' folder is not writable.', 'breeze'));
        }

        /**
         * Checking multisite specific folders.
         */
        if (is_multisite()) {
            if (is_network_admin()) {
                $file = $wp_content_dir . 'breeze-config/breeze-config.php';
                if (!is_writable($file)) {
                    self::append_permission_error($file . __(' file is not writable.', 'breeze'));
                }

                $folder_min = $wp_content_dir . 'cache/breeze-minification/';
                if (is_dir($folder_min) && !is_writable($folder_min)) {
                    self::append_permission_error($folder_min . __(' folder is not writable.', 'breeze'));
                }

                $blogs = get_sites();
                if (!empty($blogs)) {
                    foreach ($blogs as $blog_data) {
                        $blog_id = $blog_data->blog_id;
                        $folder = $wp_content_dir . 'cache/breeze/' . $blog_id . '/';
                        if (is_dir($folder) && !is_writable($folder)) {
                            self::append_permission_error($folder . __(' folder is not writable.', 'breeze'));
                        }

                        $folder_min = $wp_content_dir . 'cache/breeze-minification/' . $blog_id . '/';

                        if (!empty($cache_specific_folders) && is_array($cache_specific_folders)) {
                            foreach ($cache_specific_folders as $item_folder) {
                                foreach ($assets_folders as $asset_folder) {
                                    $check_folder = trailingslashit(trailingslashit($folder_min . $item_folder) . $asset_folder);

                                    if (is_dir($check_folder) && !is_writable($check_folder)) {
                                        self::append_permission_error($check_folder . __(' folder is not writable.', 'breeze'));
                                    }
                                }
                            }
                        }// endif
                    }
                }
            } else {
                $the_blog_id = get_current_blog_id();

                $inherit_option = get_blog_option($the_blog_id, 'breeze_inherit_settings');
                $inherit_option = filter_var($inherit_option, FILTER_VALIDATE_BOOLEAN);

                $folder_min = $wp_content_dir . 'cache/breeze-minification/';
                if (is_dir($folder_min) && !is_writable($folder_min)) {
                    self::append_permission_error($folder_min . __(' folder is not writable.', 'breeze'));
                }

                $file = $wp_content_dir . 'breeze-config/breeze-config-' . $the_blog_id . '.php';
                if ($inherit_option === false && file_exists($file) && !is_writable($file)) {
                    self::append_permission_error($file . __(' file is not writable.', 'breeze'));
                }

                $folder = $wp_content_dir . 'cache/breeze/' . $the_blog_id . '/';
                if (is_dir($folder) && !is_writable($folder)) {
                    self::append_permission_error($folder . __(' folder is not writable.', 'breeze'));
                }

                $folder_min = $wp_content_dir . 'cache/breeze-minification/' . $the_blog_id . '/';

                if (!empty($cache_specific_folders) && is_array($cache_specific_folders)) {
                    foreach ($cache_specific_folders as $item_folder) {
                        foreach ($assets_folders as $asset_folder) {
                            $check_folder = trailingslashit(trailingslashit($folder_min . $item_folder) . $asset_folder);

                            if (is_dir($check_folder) && !is_writable($check_folder)) {
                                self::append_permission_error($check_folder . __(' folder is not writable.', 'breeze'));
                            }
                        }
                    }
                }// endif
            }
        } else {
            $file = $wp_content_dir . 'breeze-config/breeze-config.php';
            if (!is_writable($file)) {
                self::append_permission_error($file . __(' file is not writable.', 'breeze'));
            }

            /**
             * Checking single site specific folders.
             */
            $folder_min = $wp_content_dir . 'cache/breeze-minification/';

            if (!empty($cache_specific_folders) && is_array($cache_specific_folders)) {
                foreach ($cache_specific_folders as $item_folder) {
                    foreach ($assets_folders as $asset_folder) {
                        $check_folder = trailingslashit(trailingslashit($folder_min . $item_folder) . $asset_folder);

                        if (is_dir($check_folder) && !is_writable($check_folder)) {
                            self::append_permission_error($check_folder . __(' folder is not writable.', 'breeze'));
                        }
                    }
                }
            }// endif
        }
    }

    public function display_the_errors() {
        $this->check_specific_files_folders();
        if (!empty(self::$errors)) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>' . __('Breeze settings will not reflect because there is file permission issue', 'breeze') . '</strong></p>';
            foreach (self::$errors as $message) {
                echo '<p>' . $message . '</p>';
            }
            echo '<p>';
            printf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url('https://support.cloudways.com/en/articles/5126387-how-can-i-reset-file-and-folder-permissions'),
                esc_html__('For reference please click on the KB', 'breeze')
            );
            echo '</p>';
            echo '</div>';
        }
    }
}

add_action(
    'admin_init',
    function () {
        new Breeze_File_Permissions();
    }
);
