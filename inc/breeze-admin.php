<?php
/**
 * @copyright 2017  Cloudways  https://www.cloudways.com
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
defined('ABSPATH') || exit('No direct script access allowed!');

/**
 * Load the required resources.
 *
 * Class Breeze_Admin
 */
class Breeze_Admin {
    public function __construct() {
        add_action(
            'init',
            function () {
                load_plugin_textdomain('breeze', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            }
        );

        // Add our custom action to clear cache
        add_action('breeze_clear_all_cache', [$this, 'breeze_clear_all_cache']);
        add_action('breeze_clear_varnish', [$this, 'breeze_clear_varnish']);

        if (is_admin() || php_sapi_name() === 'cli') {
            add_action('admin_init', [$this, 'admin_init']);

            //register menu
            add_action('admin_menu', [$this, 'register_menu_page']);
            add_action('network_admin_menu', [$this, 'register_network_menu_page']);

            // Add notice when installing plugin
            $first_install = get_option('breeze_first_install');
            if ($first_install === false) {
                add_option('breeze_first_install', 'yes');
            }
            if ($first_install === 'yes') {
                add_action('admin_notices', [$this, 'installing_notices']);
            }

            $config = breeze_get_option('basic_settings');

            if (isset($config['breeze-display-clean']) && $config['breeze-display-clean']) {
                //register top bar menu
                add_action('admin_bar_menu', [$this, 'register_admin_bar_menu'], 999);
            }
            /** Load admin js * */
            add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);

            add_action('wp_head', [$this, 'define_ajaxurl']);
            $this->ajax_handle();

            // Add setting buttons to plugins list page
            add_filter('plugin_action_links_' . BREEZE_BASENAME, [$this, 'breeze_add_action_links']);
            add_filter('network_admin_plugin_action_links_' . BREEZE_BASENAME, [$this, 'breeze_add_action_links_network']);
        }
    }

    /**
     * Admin Init.
     */
    public function admin_init() {
        //Check plugin requirements
        if (version_compare(PHP_VERSION, '5.3', '<')) {
            if (current_user_can('activate_plugins') && is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(__FILE__);
                add_action('admin_notices', [$this, 'breeze_show_error']);
                unset($_GET['activate']);
            }
        }

        //Do not load anything more
    }

    /**
     * define Ajax URL.
     */
    public function define_ajaxurl() {
        if (current_user_can('manage_options')) {
            echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
             </script>';
        }
    }

    /**
     * Add notice message when install plugin.
     */
    public function installing_notices() {
        $class = 'notice notice-success';
        $message = __('Thanks for installing Breeze. It is always recommended not to use more than one caching plugin at the same time. We recommend you to purge cache if necessary.', 'breeze');

        printf('<div class="%1$s"><p>%2$s <button class="button" id="breeze-hide-install-msg">' . __('Hide message', 'breeze') . '</button></p></div>', esc_attr($class), esc_html($message));
        update_option('breeze_first_install', 'no');
    }

    /**
     * Enqueue CSS and JS files required for the plugin functionality.
     */
    public function load_admin_scripts() {
        if (!wp_script_is('jquery', 'enqueued')) {
            wp_enqueue_script('jquery');
        }
        wp_enqueue_script('breeze-backend', plugins_url('assets/js/breeze-backend.js', dirname(__FILE__)), ['jquery'], BREEZE_VERSION, true);
        wp_enqueue_style('breeze-topbar', plugins_url('assets/css/topbar.css', dirname(__FILE__)));
        wp_enqueue_style('breeze-notice', plugins_url('assets/css/notice.css', dirname(__FILE__)));
        $current_screen = get_current_screen();
        if ($current_screen->base == 'settings_page_breeze' || $current_screen->base == 'settings_page_breeze-network') {
            //add css
            wp_enqueue_style('breeze-style', plugins_url('assets/css/style.css', dirname(__FILE__)));
            //js
            wp_enqueue_script('breeze-configuration', plugins_url('assets/js/breeze-configuration.js', dirname(__FILE__)), ['jquery'], BREEZE_VERSION, true);

            // Include the required jQuery UI Core & Libraries
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('jquery-ui-accordion');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-widget');
        }

        $token_name = [
            'breeze_purge_varnish' => wp_create_nonce('_breeze_purge_varnish'),
            'breeze_purge_database' => wp_create_nonce('_breeze_purge_database'),
            'breeze_purge_cache' => wp_create_nonce('_breeze_purge_cache'),
        ];

        wp_localize_script('breeze-backend', 'breeze_token_name', $token_name);
    }

    /**
     * Register menu.
     */
    public function register_menu_page() {
        //add submenu for Cloudways
        add_submenu_page('options-general.php', __('Breeze', 'breeze'), __('Breeze', 'breeze'), 'manage_options', 'breeze', [$this, 'breeze_load_page']);
    }

    /**
     * Register menu for multisite.
     */
    public function register_network_menu_page() {
        //add submenu for multisite network
        add_submenu_page('settings.php', __('Breeze', 'breeze'), __('Breeze', 'breeze'), 'manage_options', 'breeze', [$this, 'breeze_load_page']);
    }

    /**
     * Register bar menu.
     */
    public function register_admin_bar_menu(WP_Admin_Bar $wp_admin_bar) {
        if (!current_user_can('manage_options') && !current_user_can('editor')) {
            return;
        }

        $is_network = is_multisite() && is_network_admin();

        // add parent item
        $args = [
            'id' => 'breeze-topbar',
            'title' => esc_html__('Breeze', 'breeze'),
            'meta' => [
                'classname' => 'breeze',
            ],
        ];
        $wp_admin_bar->add_node($args);

        // Recreate the current URL in order to redirect to the same page on cache purge.
        $current_protocol = is_ssl() ? 'https' : 'http';
        $current_host = $_SERVER['HTTP_HOST'];
        $current_script = $_SERVER['SCRIPT_NAME'];
        $current_params = $_SERVER['QUERY_STRING'];

        if (is_multisite() && !is_subdomain_install()) {
            $blog_details = get_blog_details();
            $current_host .= rtrim($blog_details->path, '/');
        }

        $current_screen_url = $current_protocol . '://' . $current_host . $current_script . '?' . $current_params;
        $current_screen_url = remove_query_arg(['breeze_purge', '_wpnonce'], $current_screen_url);

        // add purge all item
        $args = [
            'id' => 'breeze-purge-all',
            'title' => (!is_multisite() || $is_network) ? esc_html__('Purge All Cache', 'breeze') : esc_html__('Purge Site Cache', 'breeze'),
            'parent' => 'breeze-topbar',
            'href' => esc_url(wp_nonce_url(add_query_arg('breeze_purge', 1, $current_screen_url), 'breeze_purge_cache')),
            'meta' => ['class' => 'breeze-toolbar-group'],
        ];
        $wp_admin_bar->add_node($args);

        // Editor role can only use Purge all cache option
        if (!current_user_can('manage_options')) {
            return;
        }

        // add purge modules group
        $args = [
            'id' => 'breeze-purge-modules',
            'title' => esc_html__('Purge Modules', 'breeze'),
            'parent' => 'breeze-topbar',
            'meta' => ['class' => 'breeze-toolbar-group'],
        ];
        $wp_admin_bar->add_node($args);

        // add child item (Purge Modules)
        $args = [
            'id' => 'breeze-purge-varnish-group',
            'title' => esc_html__('Purge Varnish Cache', 'breeze'),
            'parent' => 'breeze-purge-modules',
        ];
        $wp_admin_bar->add_node($args);

        // add child item (Purge Modules)
        $args = [
            'id' => 'breeze-purge-file-group',
            'title' => esc_html__('Purge Internal Cache', 'breeze'),
            'parent' => 'breeze-purge-modules',
        ];
        $wp_admin_bar->add_node($args);

        // add settings item
        $args = [
            'id' => 'breeze-settings',
            'title' => esc_html__('Settings', 'breeze'),
            'parent' => 'breeze-topbar',
            'href' => $is_network ? network_admin_url('settings.php?page=breeze') : admin_url('options-general.php?page=breeze'),
            'meta' => ['class' => 'breeze-toolbar-group'],
        ];
        $wp_admin_bar->add_node($args);

        // add support item
        $args = [
            'id' => 'breeze-support',
            'title' => esc_html__('Support', 'breeze'),
            'href' => 'https://support.cloudways.com/breeze-wordpress-cache-configuration',
            'parent' => 'breeze-topbar',
            'meta' => [
                'class' => 'breeze-toolbar-group',
                'target' => '_blank',
            ],
        ];
        $wp_admin_bar->add_node($args);
    }

    /**
     * Load plugin settings page for back-end.
     */
    public function breeze_load_page() {
        if (isset($_GET['page']) && $_GET['page'] === 'breeze') {
            require_once BREEZE_PLUGIN_DIR . 'views/breeze-setting-views.php';
        }
    }

    /**
     * Error displayed of the PHP version is to low.
     */
    public function breeze_show_error() {
        echo '<div class="error"><p><strong>Breeze</strong> need at least PHP 5.3 version, please update php before installing the plugin.</p></div>';
    }

    /**
     * Admin ajax actions.
     */
    public function ajax_handle() {
        add_action('wp_ajax_breeze_purge_varnish', ['Breeze_Configuration', 'purge_varnish_action']);
        add_action('wp_ajax_breeze_purge_file', ['Breeze_Configuration', 'breeze_ajax_clean_cache']);
        add_action('wp_ajax_breeze_purge_database', ['Breeze_Configuration', 'breeze_ajax_purge_database']);
    }

    /*
     * Register active plugin hook.
     */
    public static function plugin_active_hook() {
        WP_Filesystem();
        // Default basic
        $basic = breeze_get_option('basic_settings');
        if (empty($basic)) {
            $basic = [];
        }
        $default_basic = [
            'breeze-active' => '1',
            'breeze-ttl' => '',
            'breeze-minify-html' => '0',
            'breeze-minify-css' => '0',
            'breeze-minify-js' => '0',
            'breeze-gzip-compression' => '1',
            'breeze-desktop-cache' => '1',
            'breeze-browser-cache' => '1',
            'breeze-mobile-cache' => '1',
            'breeze-disable-admin' => '1',
            'breeze-display-clean' => '1',
            'breeze-include-inline-js' => '0',
            'breeze-include-inline-css' => '0',
        ];
        $basic = array_merge($default_basic, $basic);

        // Default Advanced
        $advanced = breeze_get_option('advanced_settings');
        if (empty($advanced)) {
            $advanced = [];
        }
        $default_advanced = [
            'breeze-exclude-urls' => [],
            'breeze-group-css' => '0',
            'breeze-group-js' => '0',
            'breeze-exclude-css' => [],
            'breeze-exclude-js' => [],
            'breeze-move-to-footer-js' => [],
            'breeze-defer-js' => [],
        ];
        $advanced = array_merge($default_advanced, $advanced);

        //CDN default
        $cdn = breeze_get_option('cdn_integration');
        if (empty($cdn)) {
            $cdn = [];
        }
        $wp_content = substr(WP_CONTENT_DIR, strlen(ABSPATH));
        $default_cdn = [
            'cdn-active' => '0',
            'cdn-url' => '',
            'cdn-content' => ['wp-includes', $wp_content],
            'cdn-exclude-content' => ['.php'],
            'cdn-relative-path' => '1',
        ];
        $cdn = array_merge($default_cdn, $cdn);

        // Varnish default
        $varnish = breeze_get_option('varnish_cache');
        if (empty($varnish)) {
            $varnish = [];
        }
        $default_varnish = [
            'auto-purge-varnish' => '1',
        ];
        $varnish = array_merge($default_varnish, $varnish);

        if (is_multisite()) {
            $blogs = get_sites();
            foreach ($blogs as $blog) {
                $blog_basic = get_blog_option((int) $blog->blog_id, 'breeze_basic_settings', '');
                if (empty($blog_basic)) {
                    update_blog_option((int) $blog->blog_id, 'breeze_basic_settings', $basic);
                }

                $blog_advanced = get_blog_option((int) $blog->blog_id, 'breeze_advanced_settings', '');
                if (empty($blog_advanced)) {
                    update_blog_option((int) $blog->blog_id, 'breeze_advanced_settings', $advanced);
                }

                $blog_cdn = get_blog_option((int) $blog->blog_id, 'breeze_cdn_integration', '');
                if (empty($blog_cdn)) {
                    update_blog_option((int) $blog->blog_id, 'breeze_cdn_integration', $cdn);
                }

                $blog_varnish = get_blog_option((int) $blog->blog_id, 'breeze_varnish_cache', '');
                if (empty($blog_varnish)) {
                    update_blog_option((int) $blog->blog_id, 'breeze_varnish_cache', $varnish);
                }
            }
        } else {
            update_option('breeze_basic_settings', $basic);
            update_option('breeze_advanced_settings', $advanced);
            update_option('breeze_cdn_integration', $cdn);
            update_option('breeze_varnish_cache', $varnish);
        }

        //add header to htaccess if setting is enabled or by default if first installed
        Breeze_Configuration::update_htaccess();
        //automatic config start cache
        Breeze_ConfigCache::factory()->write();
        Breeze_ConfigCache::factory()->write_config_cache();

        if (!empty($basic) && !empty($basic['breeze-active'])) {
            Breeze_ConfigCache::factory()->toggle_caching(true);
        }
    }

    /*
     * Register deactivate plugin hook.
     */
    public static function plugin_deactive_hook() {
        WP_Filesystem();
        Breeze_ConfigCache::factory()->clean_up();
        Breeze_ConfigCache::factory()->clean_config();
        Breeze_ConfigCache::factory()->toggle_caching(false);
        Breeze_Configuration::update_htaccess(true);
    }

    /*
     * Render tab for the settings in back-end.
     */
    public static function render($tab) {
        require_once BREEZE_PLUGIN_DIR . 'views/tabs/' . $tab . '.php';
    }

    /**
     * Check varnish cache exist.
     *
     * @return bool
     */
    public static function check_varnish() {
        if (isset($_SERVER['HTTP_X_VARNISH'])) {
            return true;
        }

        return false;
    }

    /**
     * Applied to the list of links to display on the plugins page.
     *
     * @param array $links List of links.
     *
     * @return array
     */
    public function breeze_add_action_links($links) {
        $my_links = [
            '<a href="' . admin_url('options-general.php?page=breeze') . '">Settings</a>',
        ];

        return array_merge($my_links, $links);
    }

    /**
     * Applied to the list of links to display on the network plugins page
     *
     * @param array $links List of links.
     *
     * @return array
     */
    public function breeze_add_action_links_network($links) {
        $my_links = [
            '<a href="' . network_admin_url('settings.php?page=breeze') . '">Settings</a>',
        ];

        return array_merge($my_links, $links);
    }

    /**
     * Clear all cache action.
     */
    public function breeze_clear_all_cache() {
        //delete minify
        Breeze_MinificationCache::clear_minification();
        //clear normal cache
        Breeze_PurgeCache::breeze_cache_flush();
        //clear varnish cache
        $this->breeze_clear_varnish();
    }

    /**
     * Clear all varnish cache action.
     */
    public function breeze_clear_varnish() {
        $main = new Breeze_PurgeVarnish();

        $is_network = (is_network_admin() || (!empty($_POST['is_network']) && $_POST['is_network'] === 'true'));

        if (is_multisite() && $is_network) {
            $sites = get_sites();
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                $homepage = home_url() . '/?breeze';
                $main->purge_cache($homepage);
                restore_current_blog();
            }
        } else {
            $homepage = home_url() . '/?breeze';
            $main->purge_cache($homepage);
        }
    }
}

add_action(
    'init',
    function () {
        $admin = new Breeze_Admin();
    },
    0
);
