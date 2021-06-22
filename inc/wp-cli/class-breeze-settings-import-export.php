<?php

/**
 * Class that handles the export and import of Breeze options
 *
 * Class Breeze_Settings_Import_Export
 */
class Breeze_Settings_Import_Export {

	function __construct() {

		// Logged in users only action.
		add_action( 'wp_ajax_breeze_export_json', array( &$this, 'export_json_settings' ) );
		add_action( 'wp_ajax_breeze_import_json', array( &$this, 'import_json_settings' ) );


	}

	/**
	 * Import settings using interface in back-end.
	 * @since 1.2.2
	 * @access public
	 */
	public function import_json_settings() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( new WP_Error( 'authority_issue', __( 'Only administrator can import settings', 'breeze' ) ) );

		}

		if ( isset( $_FILES['breeze_import_file'] ) ) {
			$allowed_extension = array( 'json' );
			$temp              = explode( '.', $_FILES['breeze_import_file']['name'] );
			$extension         = strtolower( end( $temp ) );

			if ( ! in_array( $extension, $allowed_extension, true ) ) {
				wp_send_json_error( new WP_Error( 'ext_err', __( 'The provided file is not a JSON', 'breeze' ) ) );
			}

			if ( 'application/json' !== $_FILES['breeze_import_file']['type'] ) {
				wp_send_json_error( new WP_Error( 'format_err', __( 'The provided file is not a JSON file.', 'breeze' ) ) );
			}

			$get_file_content = file_get_contents( $_FILES['breeze_import_file']['tmp_name'] );
			$json             = json_decode( trim( $get_file_content ), true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				if (
					isset( $json['breeze_basic_settings'] ) &&
					isset( $json['breeze_advanced_settings'] ) &&
					isset( $json['breeze_cdn_integration'] )
				) {
					$level = '';
					if ( is_multisite() ) {
						$level = ( isset( $_POST['network_level'] ) ) ? trim( $_POST['network_level'] ) : '';
					}
					$action = $this->replace_options( $json, $level );
					if ( false === $action ) {
						wp_send_json_error( new WP_Error( 'option_read', __( 'Could not read the options from the provided JSON file', 'breeze' ) ) );
					} elseif ( true !== $action ) {
						wp_send_json_error( new WP_Error( 'error_meta', $action ) );
					}


					wp_send_json_success( __( "Settings imported successfully. \nPage will reload", 'breeze' ) );
				}
				wp_send_json_error( new WP_Error( 'incorrect_content', __( 'The JSON content is not valid', 'breeze' ) ) );

			} else {
				wp_send_json_error( new WP_Error( 'invalid_file', __( 'The JSON file is not valid', 'breeze' ) . ': ' . json_last_error_msg() ) );

			}


		} else {
			wp_send_json_error( new WP_Error( 'file_not_set', __( 'The JSON file is missing', 'breeze' ) ) );
		}


	}

	/**
	 * Export settings using interface in back-end.
	 *
	 * @since 1.2.2
	 * @access public
	 */
	public function export_json_settings() {
		$level = '';
		if ( is_multisite() ) {
			$level = ( isset( $_GET['network_level'] ) ) ? $_GET['network_level'] : '';
		}
		$response = self::read_options( $level );

		header( 'Content-disposition: attachment; filename=breeze-export-settings-' . date_i18n( 'd-m-Y' ) . '.json' );
		header( 'Content-type: application/json' );

		wp_send_json( $response );

	}

	/**
	 * Reading all the options and return as array.
	 *
	 * @param string $level empty for single site, network for root multisite, numeric for subside ID.
	 *
	 * @return array
	 * @since 1.2.2
	 * @access public
	 * @static
	 */
	public static function read_options( $level = '' ) {
		$export = array();
		// For multisite
		if ( is_multisite() ) {
			// If this export is made from network admin
			if ( 'network' === $level ) {
				$breeze_basic_settings    = get_site_option( 'breeze_basic_settings' );
				$breeze_advanced_settings = get_site_option( 'breeze_advanced_settings' );
				$breeze_cdn_integration   = get_site_option( 'breeze_cdn_integration' );
				$breeze_varnish_cache     = get_site_option( 'breeze_varnish_cache' );

				// Extra options
				$breeze_first_install         = get_site_option( 'breeze_first_install' );
				$breeze_advanced_settings_120 = get_site_option( 'breeze_advanced_settings_120' );
			} else { // if this export is made from sub-site.
				$network_id               = $level;
				$breeze_basic_settings    = get_blog_option( $network_id, 'breeze_basic_settings' );
				$breeze_advanced_settings = get_blog_option( $network_id, 'breeze_advanced_settings' );
				$breeze_cdn_integration   = get_blog_option( $network_id, 'breeze_cdn_integration' );
				$breeze_varnish_cache     = get_blog_option( $network_id, 'breeze_varnish_cache' );

				// Extra options
				$breeze_first_install         = get_blog_option( $network_id, 'breeze_first_install' );
				$breeze_inherit_settings      = get_blog_option( $network_id, 'breeze_inherit_settings' );
				$breeze_ecommerce_detect      = get_blog_option( $network_id, 'breeze_ecommerce_detect' );
				$breeze_advanced_settings_120 = get_blog_option( $network_id, 'breeze_advanced_settings_120' );
			}
		} else { // If WP is single site.
			$breeze_basic_settings    = get_option( 'breeze_basic_settings' );
			$breeze_advanced_settings = get_option( 'breeze_advanced_settings' );
			$breeze_cdn_integration   = get_option( 'breeze_cdn_integration' );
			$breeze_varnish_cache     = get_option( 'breeze_varnish_cache' );

			// Extra options
			$breeze_first_install         = get_option( 'breeze_first_install' );
			$breeze_inherit_settings      = get_option( 'breeze_inherit_settings' );
			$breeze_ecommerce_detect      = get_option( 'breeze_ecommerce_detect' );
			$breeze_advanced_settings_120 = get_option( 'breeze_advanced_settings_120' );
		}

		$export['breeze_basic_settings']    = $breeze_basic_settings;
		$export['breeze_advanced_settings'] = $breeze_advanced_settings;
		$export['breeze_cdn_integration']   = $breeze_cdn_integration;
		$export['breeze_varnish_cache']     = $breeze_varnish_cache;

		// Extra options
		if ( isset( $breeze_first_install ) ) {
			$export['breeze_first_install'] = $breeze_first_install;
		}
		if ( isset( $breeze_inherit_settings ) ) {
			$export['breeze_inherit_settings'] = $breeze_inherit_settings;
		}
		if ( isset( $breeze_ecommerce_detect ) ) {
			$export['breeze_ecommerce_detect'] = $breeze_ecommerce_detect;
		}
		if ( isset( $breeze_advanced_settings_120 ) ) {
			$export['breeze_advanced_settings_120'] = $breeze_advanced_settings_120;
		}

		return $export;
	}

	/**
	 * Import settings using interface in back-end.
	 *
	 * @param array $options The array with options from import action.
	 * @param string $level empty for single site, network for root multisite, numeric for subside ID.
	 *
	 * @return bool|string
	 *
	 * @access public
	 * @since 1.2.2
	 */
	public function replace_options( $options = array(), $level = '' ) {
		if ( empty( $options ) ) {
			return false;
		}

		$message = '';
		// For multisite
		if ( is_multisite() ) {
			// If this export is made from network admin
			if ( 'network' === $level ) {
				foreach ( $options as $meta_key => $meta_value ) {
					if ( false !== strpos( $meta_key, 'breeze_' ) ) {
						update_site_option( $meta_key, $meta_value );
					} else {
						// $meta_key was not imported
						$message .= $meta_key . ' ' . __( 'was not imported', 'breeze' ) . '<br/>';
					}
				}

				Breeze_ConfigCache::factory()->write_config_cache( true );
			} else {

				$blog_id = absint( $level );
				foreach ( $options as $meta_key => $meta_value ) {

					if ( false !== strpos( $meta_key, 'breeze_' ) ) {
						update_blog_option( $blog_id, $meta_key, $meta_value );
					} else {
						// $meta_key was not imported
						$message .= $meta_key . ' ' . __( 'was not imported', 'breeze' ) . '<br/>';
					}
				}

				Breeze_ConfigCache::factory()->write_config_cache();
			}
		} else {

			foreach ( $options as $meta_key => $meta_value ) {
				if ( false !== strpos( $meta_key, 'breeze_' ) ) {
					update_option( $meta_key, $meta_value );
				} else {
					// $meta_key was not imported
					$message .= $meta_key . ' ' . __( 'was not imported', 'breeze' ) . '<br/>';
				}
			}
			Breeze_ConfigCache::factory()->write_config_cache();
		}

		do_action( 'breeze_clear_all_cache' );

		if ( ! empty( $message ) ) {
			return $message;
		}

		return true;
	}

	/**
	 * Import settings using WP-CLI in terminal.
	 *
	 * @param array $options The array with options from import action.
	 * @param string $level empty for single site, network for root multisite, numeric for subside ID.
	 *
	 * @return bool|string
	 * @static
	 * @since 1.2.2
	 */
	public static function replace_options_cli( $options = array(), $level = '' ) {
		if ( empty( $options ) ) {
			return false;
		}

		WP_CLI::line( 'The level is: ' . print_r( $level, true ) );// TODO remove after testing
		// For multisite
		if ( is_multisite() ) {
			WP_CLI::line( 'The WordPress install is multisite!' );
			// If this export is made from network admin
			if ( 'network' === $level ) {

				WP_CLI::line( WP_CLI::colorize( '%GUpdating%n %Mnetwork%n options' ) );

				foreach ( $options as $meta_key => $meta_value ) {

					if ( false !== strpos( $meta_key, 'breeze_' ) ) {
						update_site_option( $meta_key, $meta_value );
						WP_CLI::line( $meta_key . ' - ' . WP_CLI::colorize( '%Yimported%n' ) );
					} else {
						WP_CLI::line( $meta_key . ' - ' . WP_CLI::colorize( '%Rwas not imported%n' ) );
					}
				}

				Breeze_ConfigCache::factory()->write_config_cache( true );

			} else {

				$is_blog  = get_blog_details( $level );
				$site_url = $is_blog->siteurl;


				WP_CLI::line( WP_CLI::colorize( '%GUpdating%n %M' . $site_url . '%n options' ) );
				$blog_id = $level;

				switch_to_blog( $blog_id);

				foreach ( $options as $meta_key => $meta_value ) {
					if ( false !== strpos( $meta_key, 'breeze_' ) ) {
						update_blog_option( $blog_id, $meta_key, $meta_value );
						WP_CLI::line( $meta_key . ' - ' . WP_CLI::colorize( '%Yimported%n' ) );
					} else {
						WP_CLI::line( $meta_key . ' - ' . WP_CLI::colorize( '%Rwas not imported%n' ) );
					}
				}

				Breeze_ConfigCache::factory()->write_config_cache();

				restore_current_blog();
			}
		} else {
			WP_CLI::line( WP_CLI::colorize( '%GUpdating%n %MBreeze%n options' ) );
			foreach ( $options as $meta_key => $meta_value ) {
				if ( false !== strpos( $meta_key, 'breeze_' ) ) {
					update_option( $meta_key, $meta_value );
					WP_CLI::line( $meta_key . ' - ' . WP_CLI::colorize( '%Yimported%n' ) );
				} else {
					WP_CLI::line( $meta_key . ' - ' . WP_CLI::colorize( '%Rwas not imported%n' ) );
				}
			}

			Breeze_ConfigCache::factory()->write_config_cache();
		}

		do_action( 'breeze_clear_all_cache' );
		return true;
	}
}

new Breeze_Settings_Import_Export();
