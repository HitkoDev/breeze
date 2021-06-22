<?php
/**
 * Created by PhpStorm.
 * User: Mihai Irodiu from WPRiders
 * Date: 07.06.2021
 * Time: 13:22
 */

class Breeze_Cli_Helpers {

	/**
	 * Fetch remote JSON.
	 *
	 * @param $url - remote JSON url
	 *
	 * @since 1.2.2
	 * @access public
	 * @static
	 */
	public static function fetch_remote_json( $url ) {
		$rop_user_agent = 'breeze-import-settings-system';

		$connection = curl_init( $url );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $connection, CURLOPT_USERAGENT, $rop_user_agent );
		curl_setopt( $connection, CURLOPT_REFERER, home_url() );
		curl_setopt( $connection, CURLOPT_MAXREDIRS, 3 );
		curl_setopt( $connection, CURLOPT_FOLLOWLOCATION, true );

		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
		);

		curl_setopt( $connection, CURLOPT_HTTPHEADER, $headers );

		$fetch_response = curl_exec( $connection );
		$http_code      = curl_getinfo( $connection, CURLINFO_HTTP_CODE );
		$curl_err_no    = curl_errno( $connection );
		if ( $curl_err_no ) {
			$curl_err_msg = curl_error( $connection );
		}

		curl_close( $connection );

		if ( 200 !== (int) $http_code ) {
			return new WP_Error( 'url-err', __( 'Remote file could not be reached', 'breeze' ) );
		}

		if ( $curl_err_no ) {
			return new WP_Error( 'remote-err', $curl_err_msg );
		} else {
			return $fetch_response;
		}

	}
}