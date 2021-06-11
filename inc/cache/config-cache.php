<?php
/**
 *  @copyright 2017  Cloudways  https://www.cloudways.com
 *
 *  Original development of this plugin by JoomUnited https://www.joomunited.com/
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
//Based on some work of simple-cache
if (!defined('ABSPATH')) {
    exit;
}

class Breeze_ConfigCache {
    /**
     * Create advanced-cache file
     */
    public function write() {
        global $wp_filesystem;

        $file = trailingslashit(WP_CONTENT_DIR) . '/advanced-cache.php';

        // Create array of configuration files and their corresponding sites' URLs.
        $cache_configs = [
            'breeze-config' => [],
        ];
        if (is_multisite()) {
            // This is a multisite install, loop through all subsites.
            $blogs = get_sites(
                [
                    'fields' => 'ids',
                ]
            );

            foreach ($blogs as $blog_id) {
                switch_to_blog($blog_id);
                $config = breeze_get_option('basic_settings');
                if (!empty($config['breeze-active'])) {
                    $inherit_option = get_option('breeze_inherit_settings');

                    if ($inherit_option === '0') {
                        // Site uses own (custom) configuration.
                        $cache_configs["breeze-config-{$blog_id}"] = preg_replace('(^https?://)', '', site_url());
                    } else {
                        // Site uses global configuration.
                        $cache_configs['breeze-config'][] = preg_replace('(^https?://)', '', site_url());
                    }
                }
                restore_current_blog();
            }
        } else {
            $config = breeze_get_option('basic_settings');

            if (!empty($config['breeze-active'])) {
                $cache_configs['breeze-config'][] = preg_replace('(^https?://)', '', site_url());
            }
        }

        if (empty($cache_configs) || (count($cache_configs) === 1 && empty($cache_configs['breeze-config']))) {
            // No sites with caching enabled.
            $this->clean_config();
            return;
        }
        $file_string = '<?php ' .
                "\n\r" . 'defined( \'ABSPATH\' ) || exit;' .
                "\n\r" . 'define( \'BREEZE_ADVANCED_CACHE\', true );' .
                "\n\r" . 'if ( is_admin() ) { return; }' .
                "\n\r" . 'if ( ! @file_exists( \'' . BREEZE_PLUGIN_DIR . 'breeze.php\' ) ) { return; }';

        if (count($cache_configs) === 1) {
            // Only 1 config file available.
            $blog_file = trailingslashit(WP_CONTENT_DIR) . 'breeze-config/breeze-config.php';
            $file_string .= "\n\$config = '{$blog_file}';";
        } else {
            // Multiple configuration files, load appropriate one by comparing URLs.
            $file_string .= "\n\r" . '$domain = strtolower( stripslashes( $_SERVER[\'HTTP_HOST\'] ) );' .
                "\n" . 'if ( substr( $domain, -3 ) == \':80\' ) {' .
                "\n" . '	$domain = substr( $domain, 0, -3 );' .
                "\n" . '} elseif ( substr( $domain, -4 ) == \':443\' ) {' .
                "\n" . '	$domain = substr( $domain, 0, -4 );' .
                "\n" . '}';
            if (is_subdomain_install()) {
                $file_string .= "\n" . '$site_url = $domain;';
            } else {
                $file_string .= "\n" . 'list( $path ) = explode( \'?\', stripslashes( $_SERVER[\'REQUEST_URI\'] ) );' .
                    "\n" . '$path_parts = explode( \'/\', rtrim( $path, \'/\' ) );' .
                    "\n" . '$site_url = $domain . ( ! empty( $path_parts[1] ) ? \'/\' . $path_parts[1] : \'\' );';
            }

            // Create conditional blocks for each site.
            $file_string .= "\n" . 'function breeze_fetch_configuration_data( $site_url ) {';
            $file_string .= "\n\t" . '$config = array();';
            $file_string .= "\n\t" . 'switch ( $site_url ) {';
            foreach (array_reverse($cache_configs) as $filename => $urls) {
                $blog_file = trailingslashit(WP_CONTENT_DIR) . 'breeze-config/' . $filename . '.php';

                if (!is_array($urls)) {
                    $urls = [$urls];
                }

                if (empty($urls) || empty($urls[0])) {
                    continue;
                }

                foreach ($urls as $site_url) {
                    $file_string .= "\n\tcase '{$site_url}':";
                }
                $file_string .= "\n\t\t\$config = '{$blog_file}';" .
                    "\n\t\tbreak;";
            }

            $file_string .= "\n\t}";
            $file_string .= "\n\t" . 'return $config;';
            $file_string .= "\n}";
            $file_string .= "\n" . '$config = breeze_fetch_configuration_data( $site_url );';
            $file_string .= "\n" . 'if ( empty( $config ) && false === filter_var( SUBDOMAIN_INSTALL, FILTER_VALIDATE_BOOLEAN ) && true === filter_var( MULTISITE, FILTER_VALIDATE_BOOLEAN ) ) {';
            $file_string .= "\n\t" . '$config   = breeze_fetch_configuration_data( $domain );';
            $file_string .= "\n" . '}';
        }

        $file_string .= "\nif ( empty( \$config ) || ! @file_exists( \$config ) ) { return; }" .
            "\n\$GLOBALS['breeze_config'] = include \$config;" .
            "\n" . 'if ( empty( $GLOBALS[\'breeze_config\'] ) || empty( $GLOBALS[\'breeze_config\'][\'cache_options\'][\'breeze-active\'] ) ) { return; }' .
            "\n" . 'if ( @file_exists( \'' . BREEZE_PLUGIN_DIR . 'inc/cache/execute-cache.php\' ) ) {' .
            "\n" . '	include_once \'' . BREEZE_PLUGIN_DIR . 'inc/cache/execute-cache.php\';' .
            "\n" . '}' . "\n";

        return $wp_filesystem->put_contents($file, $file_string);
    }

    /**
     * Function write parameter to breeze-config.
     *
     * @param bool $create_root_config Used in multisite, to reset/create breeze-config.php file
     */
    public static function write_config_cache($create_root_config = false) {
        if ($create_root_config === true) {
            $network_id = get_current_network_id();
            $settings = get_network_option($network_id, 'breeze_basic_settings');
            $config = get_network_option($network_id, 'breeze_advanced_settings');
            $homepage_url = network_site_url();
        } else {
            $settings = breeze_get_option('basic_settings');
            $config = breeze_get_option('advanced_settings');
            $homepage_url = get_site_url();
        }

        $ecommerce_exclude_urls = [];

        $storage = [
            'homepage' => $homepage_url,
            'cache_options' => $settings,
            'disable_per_adminuser' => 0,
            'exclude_url' => [],
        ];

        if (is_multisite()) {
            $storage['blog_id'] = get_current_blog_id();
        }

        $storage['enabled-lazy-load'] = (isset($config['breeze-lazy-load']) ? $config['breeze-lazy-load'] : 0);
        $storage['use-lazy-load-native'] = (isset($config['breeze-lazy-load-native']) ? $config['breeze-lazy-load-native'] : 0);
        $storage['breeze-preload-links'] = (isset($config['breeze-preload-links']) ? $config['breeze-preload-links'] : 0);

        if (class_exists('WooCommerce')) {
            $ecommerce_exclude_urls = Breeze_Ecommerce_Cache::factory()->ecommerce_exclude_pages();
        }

        if (function_exists('EDD')) {
            $exclude_edd_pages = Breeze_Ecommerce_Cache::factory()->exclude_edd_pages();

            if (!empty($exclude_edd_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_edd_pages, $ecommerce_exclude_urls);
            }

            /**
             * Remove Easy Digital Downloads Software Licensing endpoint from cache
             */
            if (class_exists('EDD_Software_Licensing') && defined('EDD_SL_VERSION')) {
                $ecommerce_exclude_urls[] = '/edd-sl/*';
            }
        }

        /**
         * Give shop
         */
        if (function_exists('give_get_settings')) {
            $exclude_give_pages = Breeze_Ecommerce_Cache::factory()->exclude_give_pages();

            if (!empty($exclude_give_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_give_pages, $ecommerce_exclude_urls);
            }
        }

        /**
         * Big Commerce
         */
        if (function_exists('bigcommerce')) {
            $exclude_bigcommerce_pages = Breeze_Ecommerce_Cache::factory()->exclude_big_commerce_pages();

            if (!empty($exclude_bigcommerce_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_bigcommerce_pages, $ecommerce_exclude_urls);
            }
        }

        /**
         * CartFlows
         */
        if (class_exists('Cartflows_Loader') && defined('CARTFLOWS_FILE')) {
            $exclude_cartflows_pages = Breeze_Ecommerce_Cache::factory()->exclude_cart_flows_pages();

            if (!empty($exclude_cartflows_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_cartflows_pages, $ecommerce_exclude_urls);
            }
        }

        /**
         * MemberPress
         */
        if (class_exists('MeprJobs') && defined('MEPR_OPTIONS_SLUG')) {
            $exclude_memberpress_pages = Breeze_Ecommerce_Cache::factory()->exclude_member_press_pages();

            if (!empty($exclude_memberpress_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_memberpress_pages, $ecommerce_exclude_urls);
            }
        }

        /**
         * WP eCommerce
         */
        if (class_exists('WP_eCommerce')) {
            $exclude_wp_ecommerce_pages = Breeze_Ecommerce_Cache::factory()->exclude_wp_e_commerce_pages();

            if (!empty($exclude_wp_ecommerce_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_wp_ecommerce_pages, $ecommerce_exclude_urls);
            }
        }

        /**
         * Ecwid Ecommerce Shopping Cart
         */
        if (function_exists('ecwid_init_integrations') && defined('ECWID_PLUGIN_DIR')) {
            $exclude_ecwid_pages = Breeze_Ecommerce_Cache::factory()->exclude_ecwid_store_pages();

            if (!empty($exclude_ecwid_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_ecwid_pages, $ecommerce_exclude_urls);
            }
        }

        /**
         * WP EasyCart
         */
        if (defined('EC_PUGIN_NAME') && function_exists('wpeasycart_load_startup')) {
            $exclude_wp_easy_cart_pages = Breeze_Ecommerce_Cache::factory()->exclude_easy_cart_pages();

            if (!empty($exclude_wp_easy_cart_pages)) {
                $ecommerce_exclude_urls = array_merge($exclude_wp_easy_cart_pages, $ecommerce_exclude_urls);
            }
        }

        if (!empty($settings['breeze-disable-admin'])) {
            $storage['disable_per_adminuser'] = $settings['breeze-disable-admin'];
        }

        $storage['exclude_url'] = array_merge(
            $ecommerce_exclude_urls,
            !empty($config['breeze-exclude-urls']) ? $config['breeze-exclude-urls'] : []
        );

        $saved_pages = get_option('breeze_exclude_url_pages', []);

        if (!empty($saved_pages)) {
            $saved_pages_urls = [];
            foreach ($saved_pages as $page_id) {
                $saved_pages_urls[] = get_permalink($page_id);
            }

            $saved_pages_urls = array_unique($saved_pages_urls);

            $storage['exclude_url'] = array_merge(
                $saved_pages_urls,
                $storage['exclude_url']
            );
        }

        if (class_exists('WC_Facebook_Loader')) {
            $woocommerce_fb_feed_link = Breeze_Ecommerce_Cache::factory()->wc_facebook_feed();

            if (!empty($woocommerce_fb_feed_link)) {
                $storage['exclude_url'] = array_merge(
                    $woocommerce_fb_feed_link,
                    $storage['exclude_url']
                );
            }
        }

        return self::write_config($storage, $create_root_config);
    }

    /**
     * Create file config storage parameter used for cache.
     *
     * @param array $config             Options array.
     * @param bool  $create_root_config Used in multisite, to reset/create breeze-config.php file
     */
    public static function write_config($config, $create_root_config = false) {
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $config_dir = trailingslashit(WP_CONTENT_DIR) . 'breeze-config';
        $filename = 'breeze-config';
        if ($create_root_config === false && (is_multisite() && !is_network_admin())) {
            $filename .= '-' . get_current_blog_id();
        }

        $config_file = $config_dir . DIRECTORY_SEPARATOR . $filename . '.php';

        /*if (is_multisite() && !is_network_admin() && breeze_does_inherit_settings()) {
            // Site inherits network-level setting, do not create separate configuration file and remove existing configuration file.
            if ($wp_filesystem->exists($config_file)) {
                $wp_filesystem->delete($config_file, true);
            }
            return;
        }*/

        $wp_filesystem->mkdir($config_dir);

        $config_file_string = '<?php ' . "\n\r" . "defined( 'ABSPATH' ) || exit;" . "\n\r" . 'return ' . var_export($config, true) . '; ' . "\n\r";

        return $wp_filesystem->put_contents($config_file, $config_file_string);
    }

    /**
     * Turn on / off wp cache.
     *
     * @param bool $status If WP Cache is enabled or not.
     *
     * @return bool|void
     */
    public function toggle_caching($status) {
        global $wp_filesystem;
        if (defined('WP_CACHE') && $status === WP_CACHE) {
            return;
        }

        // Lets look 4 levels deep for wp-config.php
        $levels = 4;

        $file = '/wp-config.php';
        $config_path = false;

        for ($i = 1; $i <= 3; ++$i) {
            if ($i > 1) {
                $file = '/..' . $file;
            }

            if ($wp_filesystem->exists(untrailingslashit(ABSPATH) . $file)) {
                $config_path = untrailingslashit(ABSPATH) . $file;
                break;
            }
        }

        // Couldn't find wp-config.php
        if (!$config_path) {
            return false;
        }

        $config_file_string = $wp_filesystem->get_contents($config_path);

        // Config file is empty. Maybe couldn't read it?
        if (empty($config_file_string)) {
            return false;
        }

        $config_file = preg_split("#(\n|\r)#", $config_file_string);
        $line_key = false;

        foreach ($config_file as $key => $line) {
            if (!preg_match('/^\s*define\(\s*(\'|")([A-Z_]+)(\'|")(.*)/', $line, $match)) {
                continue;
            }

            if ($match[2] === 'WP_CACHE') {
                $line_key = $key;
            }
        }

        if ($line_key !== false) {
            unset($config_file[$line_key]);
        }

        $status_string = ($status) ? 'true' : 'false';

        array_shift($config_file);
        array_unshift($config_file, '<?php', "define( 'WP_CACHE', {$status_string} ); ");

        foreach ($config_file as $key => $line) {
            if ($line === '') {
                unset($config_file[$key]);
            }
        }

        if (!$wp_filesystem->put_contents($config_path, implode(PHP_EOL, $config_file))) {
            return false;
        }

        return true;
    }

    /**
     * Delete file for clean up.
     *
     * @return bool
     */
    public function clean_up() {
        global $wp_filesystem;
        $file = untrailingslashit(WP_CONTENT_DIR) . '/advanced-cache.php';

        $ret = true;

        if (!$wp_filesystem->delete($file)) {
            $ret = false;
        }

        $folder = untrailingslashit(breeze_get_cache_base_path());

        if (!$wp_filesystem->delete($folder, true)) {
            $ret = false;
        }

        $folder = untrailingslashit(WP_CONTENT_DIR) . '/cache/breeze-minification';

        if (!$wp_filesystem->delete($folder, true)) {
            $ret = false;
        }

        return $ret;
    }

    /**
     * Delete config file.
     *
     * @return mixed
     */
    public function clean_config() {
        global $wp_filesystem;

        $folder = untrailingslashit(WP_CONTENT_DIR) . '/breeze-config';
        return $wp_filesystem->delete($folder, true);
        return true;
    }

    /**
     * Singleton instance.
     *
     * @return Breeze_ConfigCache
     */
    public static function factory() {
        static $instance;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }
}
