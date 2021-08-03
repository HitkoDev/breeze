<?php

include BREEZE_PLUGIN_DIR . 'inc/wp-cli/class-breeze-cli-helpers.php';
include BREEZE_PLUGIN_DIR . 'inc/wp-cli/class-breeze-settings-import-export.php';

// Do not proceed if it's not a WP-CLI command.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class Breeze_WP_Cli_Core extends \WP_CLI_Command {

	function help( $args, $assoc_args ) {
		WP_CLI::line( '---' );
		WP_CLI::line( WP_CLI::colorize( 'Command to purge cache %Ywp breeze purge --cache=<all|varnish|local>%n:' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--cache=%n%Gall%n will clear local cache and varnish cache.' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--cache=%n%Gvarnish%n will clear varnish cache only.' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--cache=%n%Glocal%n will clear local cache only.' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--level=%n%G<blogID|network>%n (%Moptional multisite only%n) Will clear cache for the specified blogID or at network level(all sub-sites). If not specified then the default is network level.' ) );
		WP_CLI::line( '---' );

		WP_CLI::line( '---' );
		WP_CLI::line( WP_CLI::colorize( 'Command to export settings %Ywp breeze export --file-path=/path/to/folder --level=<network|blogID>%n ' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--file-path=%n%G/path/to/folder%n (%Moptional%n), default path is %C.../wp-content/uploads/breeze-export/%n' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--level=%n%G<network|blogid>%n (%Mfor multisite only%n). %Cnetwork%n as value, will export Breeze network settings. %CblogID%n value must be numeric and will export the Breeze settings for the given blogID' ) );
		WP_CLI::line( '---' );

		WP_CLI::line( '---' );
		WP_CLI::line( WP_CLI::colorize( 'Command to import settings %Ywp breeze import --file-path=/path/to/file.json --level=<network|blogID>%n ' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--file-path=%n%G/path/to/file.json%n (%Rrequired%n). You need to specify the full path to the JSON file or you can provide an URL address (e.g. https://my-domain.com/my-file.json)' ) );
		WP_CLI::line( WP_CLI::colorize( '%Y--level=%n%G<network|blogid>%n (%Mfor multisite only%n). %Cnetwork%n as value, will import to Breeze network settings. %CblogID%n value must be numeric and will import the Breeze settings for the given blogID' ) );
		WP_CLI::line( '---' );

		return;
	}

	function purge( $args, $assoc_args ) {
		$level = ( isset( $assoc_args['level'] ) && ! empty( $assoc_args['level'] ) ) ? trim( $assoc_args['level'] ) : '';

		if ( is_numeric( $level ) ) {
			$level = absint( $level );
		}

		if ( isset( $assoc_args ) && ! empty( $assoc_args ) && isset( $assoc_args['cache'] ) ) {

			if ( empty( $assoc_args['cache'] ) ) {
				WP_CLI::error(
					__( 'Specify a value for parameter --cache=<all|varnish|local>', 'breeze' )
				);

				return;
			}

			$assoc_args['cache'] = strtolower( $assoc_args['cache'] );

			if ( 'help' === $assoc_args['cache'] ) {
				Breeze_Cli_Helpers::cache_helper_display();

				return;
			}

			if ( 'all' === $assoc_args['cache'] ) {

				// Clear all cache at network level.
				if ( is_multisite() && empty( $level ) || 'network' === $level ) {

					$sites = get_sites();
					foreach ( $sites as $site ) {
						switch_to_blog( $site->blog_id );
						do_action( 'breeze_clear_all_cache' );
						restore_current_blog();
					}
					WP_CLI::success(
						__( 'Breeze all cache has been purged on network level', 'breeze' )
					);
				} else {
					if ( ! empty( $level ) ) {
						switch_to_blog( $level );
					}

					do_action( 'breeze_clear_all_cache' );

					if ( ! empty( $level ) ) {
						restore_current_blog();
					}

					WP_CLI::success(
						__( 'Breeze all cache has been purged.', 'breeze' )
					);
				}

			}

			if ( 'varnish' === $assoc_args['cache'] ) {
				// Clear varnish at network level.
				if ( is_multisite() && empty( $level ) || 'network' === $level ) {
					$sites = get_sites();
					foreach ( $sites as $site ) {
						switch_to_blog( $site->blog_id );
						do_action( 'breeze_clear_varnish' );
						restore_current_blog();
					}

					WP_CLI::success(
						__( 'Breeze Varnish cache has been purged on network level.', 'breeze' )
					);
				} else {
					if ( ! empty( $level ) ) {
						switch_to_blog( $level );
					}

					do_action( 'breeze_clear_varnish' );

					if ( ! empty( $level ) ) {
						restore_current_blog();
					}

					WP_CLI::success(
						__( 'Breeze Varnish cache has been purged.', 'breeze' )
					);
				}


			}

			if ( 'local' === $assoc_args['cache'] ) {
				// Clear local cache at network level.
				if ( is_multisite() && empty( $level ) || 'network' === $level ) {
					$sites = get_sites();
					foreach ( $sites as $site ) {
						switch_to_blog( $site->blog_id );
						//delete minify
						Breeze_MinificationCache::clear_minification();
						//clear normal cache
						Breeze_PurgeCache::breeze_cache_flush();
						restore_current_blog();
					}

					WP_CLI::success(
						__( 'Breeze local cache has been purged on network level', 'breeze' )
					);
				} else {
					if ( ! empty( $level ) ) {
						switch_to_blog( $level );
					}
					//delete minify
					Breeze_MinificationCache::clear_minification();
					//clear normal cache
					Breeze_PurgeCache::breeze_cache_flush();

					if ( ! empty( $level ) ) {
						restore_current_blog();
					}

					WP_CLI::success(
						__( 'Breeze local cache has been purged.', 'breeze' )
					);
				}

			}


		} else {
			Breeze_Cli_Helpers::cache_helper_display();
			WP_CLI::error(
				__( 'Parameter --cache=<all|varnish|local> is missing', 'breeze' )
			);

		}
	}

	/**
	 * Export settings to file using WP-CLI.
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @since 1.2.2
	 * @access public
	 */
	function export( $args, $assoc_args ) {
		WP_CLI::line( WP_CLI::colorize( '%YExporting%n Breeze settings to JSON file...: ' ) );
		$level = '';

		// Default file name.
		$breeze_file = 'breeze-export-settings-' . ( ( ! empty( $level ) ) ? $level . '-' : '' ) . date_i18n( 'd-m-Y' ) . '.json';

		if ( ! empty( $assoc_args ) && isset( $assoc_args['level'] ) && ! empty( trim( $assoc_args['level'] ) ) ) {

			if ( 'network' === trim( $assoc_args['level'] ) || is_numeric( $assoc_args['level'] ) ) {

				if ( is_string( $assoc_args['level'] ) && ! is_numeric( $assoc_args['level'] ) ) {
					$level       = trim( $assoc_args['level'] );
					$breeze_file = 'breeze-export-settings-network-' . date_i18n( 'd-m-Y' ) . '.json';
				} elseif ( is_numeric( trim( $assoc_args['level'] ) ) ) {
					$level   = absint( trim( $assoc_args['level'] ) );
					$is_blog = get_blog_details( $level );

					if ( empty( $is_blog ) ) {
						WP_CLI::error(
							__( 'The blog ID is not valid, --level=<blog_id>', 'breeze' )
						);

						return;
					}

					$breeze_file = 'breeze-export-settings-' . $level . '-' . date_i18n( 'd-m-Y' ) . '.json';
				}
			} else {
				WP_CLI::error(
					__( 'Parameter --level=<network|blog_id> does not contain valid data', 'breeze' )
				);
			}
		}
		$settings = wp_json_encode( Breeze_Settings_Import_Export::read_options( $level ) );

		$uploads             = wp_upload_dir();
		$uploads_base_folder = $uploads['basedir'];

		$breeze_export = $uploads_base_folder . '/breeze-export/';

		if ( ! empty( $assoc_args ) && isset( $assoc_args['file-path'] ) ) {
			$path = trim( $assoc_args['file-path'] );
			if ( ! empty( $path ) ) {
				$breeze_export = trailingslashit( $path );
			}
		}

		$full_file_path = $breeze_export . $breeze_file;
		$create         = false;
		if ( wp_mkdir_p( $breeze_export ) ) {
			$create = true;
		}
		if ( $create ) {
			$file_handle = @fopen( $full_file_path, 'wb' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
			if ( $file_handle ) {
				fwrite( $file_handle, $settings ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
				fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			} else {
				WP_CLI::error(
					__( 'Could not write to file', 'breeze' )
				);
			}
		}

		WP_CLI::success(
			__( 'Breeze settings have been exported to file', 'breeze' )
		);

		WP_CLI::line(
			sprintf(
			/* translators: %s Export file location */
				__( 'File location: %s', 'breeze' ),
				$full_file_path
			)
		);

		WP_CLI::line( WP_CLI::colorize( '%YDone%n.' ) );
	}

	/**
	 * Import settings using WP-CLI.
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @since 1.2.2
	 * @access public
	 */
	function import( $args, $assoc_args ) {
		if ( empty( $assoc_args ) || ! isset( $assoc_args['file-path'] ) ) {
			WP_CLI::error(
				__( 'You need to specify the --file-path=<full_path_to_file> parameter', 'breeze' )
			);

			return;
		}

		$file_path = trim( $assoc_args['file-path'] );

		if ( empty( $file_path ) ) {
			WP_CLI::error(
				__( 'You need to specify the full path to breeze JSON file', 'breeze' )
			);

			return;
		}
		if ( wp_http_validate_url( $file_path ) || filter_var( $file_path, FILTER_VALIDATE_URL ) ) {
			$contents = Breeze_Cli_Helpers::fetch_remote_json( $file_path );
			if ( is_wp_error( $contents ) ) {
				WP_CLI::error(
					__( 'Error importing remote JSON file', 'breeze' ) . ' : ' . $contents->get_error_message()
				);
			}

		} else {
			if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
				WP_CLI::error(
					__( 'The given file path is not valid or the file is not readable', 'breeze' ) . ' : ' . $file_path
				);

				return;
			}

			$handle   = fopen( $file_path, 'r' );
			$contents = fread( $handle, filesize( $file_path ) );
			fclose( $handle );
		}


		$contents = trim( $contents );

		$json = json_decode( $contents, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			WP_CLI::error(
				sprintf(
				/* translators: %s The JSON had an issue */
					__( 'There was an error running the action scheduler: %s', 'breeze' ),
					json_last_error_msg()
				)
			);

			return;
		}

		if (
			isset( $json['breeze_basic_settings'] ) &&
			isset( $json['breeze_advanced_settings'] ) &&
			isset( $json['breeze_cdn_integration'] )
		) {
			WP_CLI::success(
				__( 'The provided JSON is valid...importing data', 'breeze' )
			);

			$level = '';
			if ( ! empty( $assoc_args ) && isset( $assoc_args['level'] ) && ! empty( trim( $assoc_args['level'] ) ) ) {
				if ( 'network' === trim( $assoc_args['level'] ) || is_numeric( $assoc_args['level'] ) ) {

					if ( is_string( $assoc_args['level'] ) && ! is_numeric( $assoc_args['level'] ) ) {
						$level = trim( $assoc_args['level'] );

					} elseif ( is_numeric( trim( $assoc_args['level'] ) ) ) {
						$level   = absint( trim( $assoc_args['level'] ) );
						$is_blog = get_blog_details( $level );

						if ( empty( $is_blog ) ) {
							WP_CLI::error(
								__( 'The blog ID is not valid, --level=<blog_id>', 'breeze' )
							);

							return;
						}
					}
				} else {
					WP_CLI::error(
						__( 'Parameter --level=<network|blog_id> does not contain valid data', 'breeze' )
					);
				}
			}

			$settings_action = Breeze_Settings_Import_Export::replace_options_cli( $json, $level );

			if ( true === $settings_action ) {
				WP_CLI::success(
					__( 'Settings have been imported', 'breeze' )
				);
			} else {
				WP_CLI::error(
					__( 'Error improting the settings, check the JSON file', 'breeze' ) . ' : ' . $file_path
				);
			}
		} else {
			WP_CLI::error(
				__( 'The JSON file does not contain valid data', 'breeze' ) . ' : ' . $file_path
			);
		}

		WP_CLI::line( WP_CLI::colorize( '%YDone%n.' ) );

	}

}

WP_CLI::add_command(
	'breeze',
	'Breeze_WP_Cli_Core',
	array( 'file-path' => '' )
);



