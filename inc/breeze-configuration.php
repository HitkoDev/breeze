<?php
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

class Breeze_Configuration{
    public function __construct()
    {
		global $breeze_network_subsite_settings;
		$breeze_network_subsite_settings = false;
		add_action( 'load-settings_page_breeze', array($this,'afterLoadConfigPage') );
    }


    /*
     * function to save settings
     */
    public function afterLoadConfigPage() {
		// Per-site settings (multisite inheriting)
		if (
			is_multisite() &&
			isset( $_REQUEST['inherit-settings'] ) &&
			isset( $_POST['breeze_inherit_settings_nonce'] ) &&
			wp_verify_nonce( $_POST['breeze_inherit_settings_nonce'], 'breeze_inherit_settings' )
		) {
			$inherit_settings = ( 1 == $_REQUEST['inherit-settings'] ? '1' : '0' );
			update_option( 'breeze_inherit_settings', $inherit_settings );

			if ( ! isset( $_REQUEST['breeze_basic_action'], $_REQUEST['breeze_advanced_action'] ) ) {
				WP_Filesystem();
				Breeze_ConfigCache::factory()->write_config_cache();
			}
		}

        // Basic options tab
        if (isset($_REQUEST['breeze_basic_action']) && $_REQUEST['breeze_basic_action'] == 'breeze_basic_settings') {
            if (isset($_POST['breeze_settings_basic_nonce']) && wp_verify_nonce($_POST['breeze_settings_basic_nonce'], 'breeze_settings_basic')) {
                WP_Filesystem();

                $basic = array(
                    'breeze-active' =>(isset($_POST['cache-system']) ? '1' : '0'),
                    'breeze-ttl' => (int)$_POST['cache-ttl'],
                    'breeze-minify-html' => (isset($_POST['minification-html']) ? '1' : '0'),
                    'breeze-minify-css' => (isset($_POST['minification-css']) ? '1' : '0'),
                    'breeze-minify-js' => (isset($_POST['minification-js']) ? '1' : '0'),
                    'breeze-gzip-compression' => (isset($_POST['gzip-compression']) ? '1' : '0'),
                    'breeze-browser-cache' => (isset($_POST['browser-cache']) ? '1' : '0'),
                    'breeze-desktop-cache' => (int)$_POST['desktop-cache'],
                    'breeze-mobile-cache' => (int)$_POST['mobile-cache'],
                    'breeze-disable-admin' => (isset($_POST['breeze-admin-cache']) ? '0' : '1'), // 0 is enable, 1 is disable in this case.
                    'breeze-display-clean' => '1',
                    'breeze-include-inline-js' => (isset($_POST['include-inline-js']) ? '1' : '0'),
                    'breeze-include-inline-css' => (isset($_POST['include-inline-css']) ? '1' : '0'),
                );

                breeze_update_option( 'basic_settings', $basic, true );

                // Storage infomation to cache pages
                Breeze_ConfigCache::factory()->write();
                Breeze_ConfigCache::factory()->write_config_cache();

                // Turn on WP_CACHE to support advanced-cache file
                if (isset($_POST['cache-system'])) {
                    Breeze_ConfigCache::factory()->toggle_caching(true);
                } else {
                    Breeze_ConfigCache::factory()->toggle_caching(false);
                }

                // Reschedule cron events
                if(isset($_POST['cache-system'])){
                    Breeze_PurgeCacheTime::factory()->unschedule_events();
                    Breeze_PurgeCacheTime::factory()->schedule_events();
                }
                // Add expires header
                self::update_htaccess();

	            //delete cache after settings
	            do_action('breeze_clear_all_cache');

            }
        }
        // Advanced options tab
        if (isset($_REQUEST['breeze_advanced_action']) && $_REQUEST['breeze_advanced_action'] == 'breeze_advanced_settings') {
            if (isset($_POST['breeze_settings_advanced_nonce']) && wp_verify_nonce($_POST['breeze_settings_advanced_nonce'], 'breeze_settings_advanced')) {
                $exclude_urls = $this->string_convert_arr(sanitize_textarea_field($_POST['exclude-urls']));
                $exclude_css = $this->string_convert_arr(sanitize_textarea_field($_POST['exclude-css']));
                $exclude_js = $this->string_convert_arr(sanitize_textarea_field($_POST['exclude-js']));
                $move_to_footer_js = $defer_js = array();

                if(!empty($exclude_js)){
	                $exclude_js = array_unique($exclude_js);
                }

                if(!empty($exclude_css)){
	                $exclude_css = array_unique($exclude_css);
                }

                if (!empty($_POST['move-to-footer-js'])) {
                    foreach ($_POST['move-to-footer-js'] as $url) {
                        if (trim($url) == '') continue;
                        $url = current(explode('?', $url, 2));
                        $move_to_footer_js[sanitize_text_field($url)] = sanitize_text_field($url);
                    }
                }

                if (!empty($_POST['defer-js'])) {
                    foreach ($_POST['defer-js'] as $url) {
                        if (trim($url) == '') continue;
                        $url = current(explode('?', $url, 2));
                        $defer_js[sanitize_text_field($url)] = sanitize_text_field($url);
                    }
                }

                $advanced = array(
                    'breeze-exclude-urls' => $exclude_urls,
                    'breeze-group-css' => (isset($_POST['group-css']) ? '1' : '0'),
                    'breeze-group-js' => (isset($_POST['group-js']) ? '1' : '0'),
                    'breeze-exclude-css' => $exclude_css,
                    'breeze-exclude-js' => $exclude_js,
                    'breeze-move-to-footer-js' => $move_to_footer_js,
                    'breeze-defer-js' => $defer_js
				);

				breeze_update_option( 'advanced_settings', $advanced, true );

                WP_Filesystem();
                // Storage infomation to cache pages
                Breeze_ConfigCache::factory()->write_config_cache();

                //delete cache after settings
                do_action('breeze_clear_all_cache');

            }
        }

        // Database option tab
        if (
			isset( $_REQUEST['breeze_database_action'] ) &&
			'breeze_database_settings' === $_REQUEST['breeze_database_action'] &&
			isset( $_POST['breeze_settings_database_nonce'] ) &&
			wp_verify_nonce( $_POST['breeze_settings_database_nonce'], 'breeze_settings_database' ) &&
			! empty( $_POST['clean'] ) && is_array( $_POST['clean'] )
		) {
			self::optimize_database( $_POST['clean'] );

			//return current page
			if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
				$url = remove_query_arg( 'save-settings', $_REQUEST['_wp_http_referer'] );
				wp_safe_redirect( add_query_arg( 'database-cleanup', 'success', $url ) );
				exit;
			}
		}

        // Cdn option tab
        if (isset($_REQUEST['breeze_cdn_action']) && $_REQUEST['breeze_cdn_action'] == 'breeze_cdn_settings') {
            if (isset($_POST['breeze_settings_cdn_nonce']) && wp_verify_nonce($_POST['breeze_settings_cdn_nonce'], 'breeze_settings_cdn')) {
                $cdn_content = array();
                $exclude_content = array();
                if(!empty($_POST['cdn-content'])){
                    $cdn_content = explode(',',sanitize_text_field($_POST['cdn-content']));
                    $cdn_content = array_unique($cdn_content);
                }
                if(!empty($_POST['cdn-exclude-content'])){
                    $exclude_content = explode(',',sanitize_text_field($_POST['cdn-exclude-content']));
                    $exclude_content = array_unique($exclude_content);
                }

                $cdn = array(
                    'cdn-active' => (isset($_POST['activate-cdn']) ? '1' : '0'),
                    'cdn-url' =>(isset($_POST['cdn-url']) ? sanitize_text_field($_POST['cdn-url']) : ''),
                    'cdn-content' => $cdn_content,
                    'cdn-exclude-content' => $exclude_content,
                    'cdn-relative-path' =>(isset($_POST['cdn-relative-path']) ? '1' : '0'),
                );

				breeze_update_option( 'cdn_integration', $cdn, true );

	            //delete cache after settings
	            do_action('breeze_clear_all_cache');

            }
        }

        // Varnish option tab
        if (isset($_REQUEST['breeze_varnish_action']) && $_REQUEST['breeze_varnish_action'] == 'breeze_varnish_settings') {
            if (isset($_POST['breeze_settings_varnish_nonce']) && wp_verify_nonce($_POST['breeze_settings_varnish_nonce'], 'breeze_settings_varnish')) {
                $varnish = array(
                    'auto-purge-varnish' => (isset($_POST['auto-purge-varnish']) ? '1' : '0'),
                    'breeze-varnish-server-ip' => preg_replace('/[^a-zA-Z0-9\-\_\.]*/','',$_POST['varnish-server-ip'])
				);

				breeze_update_option( 'varnish_cache', $varnish, true );

                // Clear varnish cache after settings
	            do_action('breeze_clear_varnish');
            }
        }


        //return current page
        if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			$url = remove_query_arg( 'database-cleanup', $_REQUEST['_wp_http_referer'] );
            wp_safe_redirect( add_query_arg( 'save-settings', 'success', $url ) );
            exit;
        }

        return true;
    }

	/*
	 * function add expires header to .htaccess
	 */
	public static function add_expires_header( $clean = false, $conditional_regex = '' ) {
		$args = array(
			'before' => '#Expires headers configuration added by BREEZE WP CACHE plugin',
			'after'  => '#End of expires headers configuration',
		);

		if ( $clean ) {
			$args['clean'] = true;
		} else {
			$args['content'] = 'SetEnv BREEZE_BROWSER_CACHE_ON 1' . PHP_EOL .
				'<IfModule mod_expires.c>' . PHP_EOL .
				'   ExpiresActive On' . PHP_EOL .
				'   ExpiresByType image/gif "access 1 year"' . PHP_EOL .
				'   ExpiresByType image/jpg "access 1 year"' . PHP_EOL .
				'   ExpiresByType image/jpeg "access 1 year"' . PHP_EOL .
				'   ExpiresByType image/png "access 1 year"' . PHP_EOL .
				'   ExpiresByType image/x-icon "access 1 year"' . PHP_EOL .
				'   ExpiresByType text/css "access 1 month"' . PHP_EOL .
				'   ExpiresByType text/javascript "access 1 month"' . PHP_EOL .
				'   ExpiresByType text/html "access 1 month"' . PHP_EOL .
				'   ExpiresByType application/javascript "access 1 month"' . PHP_EOL .
				'   ExpiresByType application/x-javascript "access 1 month"' . PHP_EOL .
				'   ExpiresByType application/xhtml-xml "access 1 month"' . PHP_EOL .
				'   ExpiresByType application/pdf "access 1 month"' . PHP_EOL .
				'   ExpiresByType application/x-shockwave-flash "access 1 month"' . PHP_EOL .
				'   ExpiresDefault "access 1 month"' . PHP_EOL .
				'</IfModule>' . PHP_EOL;

			$args['conditions'] = array(
				'mod_expires',
				'ExpiresActive',
				'ExpiresDefault',
				'ExpiresByType',
			);

			if ( ! empty( $conditional_regex ) ) {
				$args['content'] = '<If "' . $conditional_regex . '">' . PHP_EOL . $args['content'] . '</If>' . PHP_EOL;
			};
		}

		return self::write_htaccess( $args );
	}

	/*
	 * function add gzip header to .htaccess
	 */
	public static function add_gzip_htacess( $clean = false, $conditional_regex = '' ) {
		$args = array(
			'before' => '# Begin GzipofBreezeWPCache',
			'after'  => '# End GzipofBreezeWPCache',
		);

		if ( $clean ) {
			$args['clean'] = true;
		} else {
			$args['content'] = 'SetEnv BREEZE_GZIP_ON 1' . PHP_EOL .
				'<IfModule mod_deflate.c>' . PHP_EOL .
				'	AddType x-font/woff .woff' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE image/svg+xml' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE text/plain' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE text/html' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE text/xml' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE text/css' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE text/javascript' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE application/xml' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE application/xhtml+xml' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE application/rss+xml' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE application/javascript' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE application/x-javascript' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE application/x-font-ttf' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE application/vnd.ms-fontobject' . PHP_EOL .
				'	AddOutputFilterByType DEFLATE font/opentype font/ttf font/eot font/otf' . PHP_EOL .
				'</IfModule>' . PHP_EOL;

			$args['conditions'] = array(
				'mod_deflate',
				'AddOutputFilterByType',
				'AddType',
				'GzipofBreezeWPCache',
			);

			if ( ! empty( $conditional_regex ) ) {
				$args['content'] = '<If "' . $conditional_regex . '">' . PHP_EOL . $args['content'] . '</If>' . PHP_EOL;
			};
		}

		return self::write_htaccess( $args );
	}

	/**
	 * Trigger update to htaccess file.
	 *
	 * @param bool $clean If true, will clear custom .htaccess rules.
	 * @return bool
	 */
	public static function update_htaccess( $clean = false ) {
		if ( $clean ) {
			self::add_expires_header( $clean );
			self::add_gzip_htacess( $clean );
			return true;
		}

		if ( is_multisite() ) {
			// Multisite setup.
			$supports_conditionals = breeze_is_supported( 'conditional_htaccess' );

			if ( ! $supports_conditionals ) {
				// If Apache htaccess conditional directives not available, inherit network-level settings.
				$config = get_site_option( 'breeze_basic_settings', array() );

				if ( isset( $config['breeze-active'] ) && '1' === $config['breeze-active'] ) {
					self::add_expires_header( ! isset( $config['breeze-browser-cache'] ) || '1' !== $config['breeze-browser-cache'] );
					self::add_gzip_htacess( ! isset( $config['breeze-gzip-compression'] ) || '1' !== $config['breeze-gzip-compression'] );
				} else {
					self::add_expires_header( true );
					self::add_gzip_htacess( true );
				}

				return true;
			}

			$has_browser_cache      = false;
			$browser_cache_sites    = array();
			$no_browser_cache_sites = array();
			$browser_cache_regex    = '';
			$has_gzip_compress      = false;
			$gzip_compress_sites    = array();
			$no_gzip_compress_sites = array();
			$gzip_compress_regex    = '';

			$blogs = get_sites(
				array(
					'fields' => 'ids',
				)
			);

			global $breeze_network_subsite_settings;
			$breeze_network_subsite_settings = true;

			foreach ( $blogs as $blog_id ) {
				switch_to_blog( $blog_id );
				$site_url = preg_quote( preg_replace( '(^https?://)', '', site_url() ) );
				$config   = breeze_get_option( 'basic_settings' );
				if ( '1' === $config['breeze-active'] ) {
					if ( '1' === $config['breeze-browser-cache'] ) {
						$has_browser_cache     = true;
						$browser_cache_sites[] = $site_url;
					} else {
						$no_browser_cache_sites[] = $site_url;
					}
					if ( '1' === $config['breeze-gzip-compression'] ) {
						$has_gzip_compress     = true;
						$gzip_compress_sites[] = $site_url;
					} else {
						$no_gzip_compress_sites[] = $site_url;
					}
				} else {
					$no_browser_cache_sites[] = $site_url;
					$no_gzip_compress_sites[] = $site_url;
				}
				restore_current_blog();
			}

			$breeze_network_subsite_settings = false;

			$rules = array(
				'browser_cache' => 'add_expires_header',
				'gzip_compress' => 'add_gzip_htacess',
			);
			// Loop through caching type rules.
			foreach ( $rules as $var_name => $method_name ) {
				$has_cache_var = 'has_' . $var_name;
				if ( ! $$has_cache_var ) {
					// No sites using rules, clean up.
					self::$method_name( true );
				} else {
					$enabled_sites  = $var_name . '_sites';
					$disabled_sites = 'no_' . $var_name . '_sites';
					$regex_string   = '';

					if ( empty( $$disabled_sites ) ) {
						// Rule is active across sites, do not include conditional directives.
						self::$method_name( $clean );
						continue;
					}

					if ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ) {
						// Subdomain sites are matched using host alone.
						$regex_string = '%{HTTP_HOST} =~ m#^(' . implode( '|', $$enabled_sites ) . ')#';
					} else {
						// Subdirectory sites are matched using "THE_REQUEST".
						$network_site_url = preg_quote( preg_replace( '(^https?://)', '', untrailingslashit( network_site_url() ) ) );

						// Remove host part from URLs.
						$$enabled_sites = array_filter(
							array_map(
								function( $url ) use ( $network_site_url ) {
									$modified = str_replace( $network_site_url, '', $url );
									return empty( $modified ) ? '/' : $modified;
								},
								$$enabled_sites
							)
						);

						if ( ! empty( $$enabled_sites ) ) {
							$regex_string = '%{THE_REQUEST} =~ m#^GET (' . implode( '|', $$enabled_sites ) . ')#';
						}

						// Remove main site URL from disabled sites array.
						$network_site_url_index = array_search( $network_site_url, $$disabled_sites );
						if ( false !== $network_site_url_index ) {
							unset( $$disabled_sites[ $network_site_url_index ] );
						}
						// Remove host part from URLs.
						$$disabled_sites = array_filter(
							array_map(
								function( $url ) use ( $network_site_url ) {
									$modified = str_replace( $network_site_url, '', $url );
									return empty( $modified ) ? '/' : $modified;
								},
								$$disabled_sites
							)
						);
						if ( ! empty( $$disabled_sites ) ) {
							if ( ! empty( $$enabled_sites ) ) {
								$regex_string .= ' && ';
							}
							$regex_string .= '%{THE_REQUEST} !~ m#^GET (' . implode( '|', $$disabled_sites ) . ')#';
						}
					}

					// Add conditional rule.
					self::$method_name( empty( $regex_string ), $regex_string );
				}
			}
		} else {
			// Single-site setup.
			$config = breeze_get_option( 'basic_settings' );
			if ( '1' === $config['breeze-active'] ) {
				self::add_expires_header( '1' !== $config['breeze-browser-cache'] );
				self::add_gzip_htacess( '1' !== $config['breeze-gzip-compression'] );
			} else {
				// Caching not activated, clean up.
				self::add_expires_header( true );
				self::add_gzip_htacess( true );
				return true;
			}
		}

		return true;
	}

	/**
	 * Add and remove custom blocks from .htaccess.
	 *
	 * @param array $args
	 * @return bool
	 */
	public static function write_htaccess( $args ) {
		$htaccess_path = trailingslashit( ABSPATH ) . '.htaccess';

		if ( ! is_super_admin() ) {
			return false;
		}
		// open htaccess file
		if ( file_exists( $htaccess_path ) ) {
			$htaccess_content = file_get_contents( $htaccess_path );
		}
		if ( empty( $htaccess_content ) ) {
			return false;
		}

		// Remove old rules.
		$htaccess_content = preg_replace( "/{$args['before']}[\s\S]*{$args['after']}" . PHP_EOL . '/im', '', $htaccess_content );

		if ( ! isset( $args['clean'] ) ) {
			if ( isset( $args['conditions'] ) ) {
				foreach ( $args['conditions'] as $condition ) {
					if ( strpos( $htaccess_content, $condition ) !== false ) {
						return false;
					}
				}
			}

			$htaccess_content = $args['before'] . PHP_EOL . $args['content'] . $args['after'] . PHP_EOL . $htaccess_content;
		}

		file_put_contents( $htaccess_path, $htaccess_content );
		return true;
	}

    /*
    * Database clean tab
    * funtion to clean in database
    */
    public static function cleanSystem($type){
        global $wpdb;
        $clean = "";

        switch ($type){
            case "revisions":
                $clean = "DELETE FROM `$wpdb->posts` WHERE post_type = 'revision';";
                $revisions = $wpdb->query( $clean );

                $message = "All post revisions";
                break;
            case "drafted":
                $clean = "DELETE FROM `$wpdb->posts` WHERE post_status = 'auto-draft';";
                $autodraft = $wpdb->query( $clean );

                $message = "All auto drafted content";
                break;
            case "trash":
                $clean = "DELETE FROM `$wpdb->posts` WHERE post_status = 'trash';";
                $posttrash = $wpdb->query( $clean );

                $message = "All trashed content";
                break;
            case "comments":
                $clean = "DELETE FROM `$wpdb->comments` WHERE comment_approved = 'spam' OR comment_approved = 'trash' ;";
                $comments = $wpdb->query( $clean );

                $message = "Comments from trash & spam";
                break;
            case "trackbacks":
                $clean = "DELETE FROM `$wpdb->comments` WHERE comment_type = 'trackback' OR comment_type = 'pingback' ;";
                $comments = $wpdb->query( $clean );

                $message = "Trackbacks and pingbacks";
                break;
            case "transient":
                $clean = "DELETE FROM `$wpdb->options` WHERE option_name LIKE '%\_transient\_%' ;";
                $comments = $wpdb->query( $clean );

                $message = "Transient options";
                break;
        }

        return true;
    }

    /**
     * Database clean tab
     * funtion to get number of element to clean in database
	 *
	 * @param string $type
     */
    public static function getElementToClean( $type ) {
        global $wpdb;
        $return = 0;
        switch ($type){
            case "revisions":
                $element = "SELECT ID FROM `$wpdb->posts` WHERE post_type = 'revision';";
                $return = $wpdb->query( $element );
                break;
            case "drafted":
                $element = "SELECT ID FROM `$wpdb->posts` WHERE post_status = 'auto-draft';";
                $return = $wpdb->query( $element );
                break;
            case "trash":
                $element = "SELECT ID FROM `$wpdb->posts` WHERE post_status = 'trash';";
                $return = $wpdb->query( $element );
                break;
            case "comments":
                $element = "SELECT comment_ID FROM `$wpdb->comments` WHERE comment_approved = 'spam' OR comment_approved = 'trash' ;";
                $return = $wpdb->query( $element );
                break;
            case "trackbacks":
                $element = "SELECT comment_ID FROM `$wpdb->comments` WHERE comment_type = 'trackback' OR comment_type = 'pingback' ;";
                $return = $wpdb->query( $element );
                break;
            case "transient":
                $element = "SELECT option_id FROM `$wpdb->options` WHERE option_name LIKE '%\_transient\_%' AND option_name != '_transient_doing_cron' ;";
                $return = $wpdb->query( $element );
                break;
        }
        return $return;
    }

    // Convert string to array
    protected function string_convert_arr($input){
        $output = array();
        if(!empty($input)){
            $input = rawurldecode($input);
            $input = trim($input);
            $input = str_replace(' ', '', $input);
            $input = explode("\n",$input);

            foreach ($input as $k => $v){
                $output[] = trim($v);
            }
        }
        return $output;
    }
    //ajax clean cache
    public static function breeze_clean_cache() {
       // Check whether we're clearing the cache for one subsite on the network.
		$is_subsite = is_multisite() && ! is_network_admin();

        // analysis size cache
		$cachepath = untrailingslashit( breeze_get_cache_base_path( is_network_admin() ) );

		$size_cache = breeze_get_directory_size( $cachepath );

		// Analyze minification directory sizes.
		$files_path = rtrim( WP_CONTENT_DIR, '/' ) . '/cache/breeze-minification';
		if ( $is_subsite ) {
			$blog_id = get_current_blog_id();
			$files_path .= DIRECTORY_SEPARATOR . $blog_id;
		}
		$size_cache += breeze_get_directory_size( $files_path, array( 'index.html' ) );

        $result = self::formatBytes( $size_cache );

		//delete minify file
		Breeze_MinificationCache::clear_minification();
        //delete all cache
        Breeze_PurgeCache::breeze_cache_flush();

        return $result;
    }

    /*
     *Ajax clean cache
     *
     */
    public static function breeze_ajax_clean_cache(){
        //check security nonce
        check_ajax_referer( '_breeze_purge_cache', 'security' );
        $result = self::breeze_clean_cache();

        echo json_encode($result);
        exit;
    }
    /*
     * Ajax purge varnish
     */
    public static function purge_varnish_action(){
        //check security
        check_ajax_referer( '_breeze_purge_varnish', 'security' );

	    do_action('breeze_clear_varnish');

        echo json_encode(array('clear' => true));
        exit;
    }
    /*
     * Ajax purge database
     */
    public static function breeze_ajax_purge_database(){
        //check security
        check_ajax_referer( '_breeze_purge_database', 'security' );

        $type = array('revisions','drafted','trash','comments','trackbacks','transient');
        self::optimize_database( $type );

        echo json_encode(array('clear' => true));
        exit;
    }
    public static function formatBytes($bytes, $precision = 2) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2);
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2);
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2);
        } elseif ($bytes > 1) {
            $bytes = $bytes;
        } elseif ($bytes == 1) {
            $bytes = $bytes;
        } else {
            $bytes = '0';
        }

        return $bytes;
	}

	/**
	 * Perform database optimization.
	 *
	 * @param array $items
	 */
	public static function optimize_database( $items ) {
		if ( is_multisite() && is_network_admin() ) {
			$sites = get_sites(
				array(
					'fields' => 'ids',
				)
			);

			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				foreach ( $items as $item ) {
					self::cleanSystem( $item );
				}
				restore_current_blog();
			}
		} else {
			foreach ( $items as $item ) {
				self::cleanSystem( $item );
			}
		}
	}

}

//init configuration object
new Breeze_Configuration();
