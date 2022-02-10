<?php
/**
 * SiteGround Helper Service.
 */

namespace SiteGround_Helper;

/**
 * SiteGround_Helper_Service class.
 */
class Helper_Service {

	/**
	 * Load the global wp_filesystem.
	 *
	 * @since  1.0.0
	 *
	 * @return object The instance.
	 */
	public static function setup_wp_filesystem() {
		global $wp_filesystem;

		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Check if wp cron is disabled and send error message.
	 *
	 * @since  1.0.0
	 */
	public static function is_cron_disabled() {
		if ( defined( 'DISABLE_WP_CRON' ) && true == DISABLE_WP_CRON ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Hide warnings in rest api.
	 *
	 * @since  1.0.0
	 */
	public function hide_warnings_in_rest_api() {
		if ( self::is_rest() ) {
			error_reporting( E_ERROR | E_PARSE );
		}
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if it's rest request, false otherwise.
	 */
	public static function is_rest() {
		$prefix = rest_get_url_prefix();

		if (
			defined( 'REST_REQUEST' ) && REST_REQUEST ||
			(
				isset( $_GET['rest_route'] ) &&
				0 === @strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 )
			)
		) {
			return true;
		}

		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );

		return 0 === @strpos( $current_url['path'], $rest_url['path'], 0 );
	}

	/**
	 * Some plugins like WPML for example are overwriting the home url.
	 *
	 * @since  1.0.0
	 *
	 * @return string The real home url.
	 */
	public static function get_home_url() {
		$url = get_option( 'home' );

		$scheme = is_ssl() ? 'https' : parse_url( $url, PHP_URL_SCHEME );

		$url = set_url_scheme( $url, $scheme );

		return trailingslashit( $url );
	}

	/**
	 * Some plugins like WPML for example are overwriting the site url.
	 *
	 * @since  1.0.0
	 *
	 * @return string The real site url.
	 */
	public static function get_site_url() {
		$url = get_option( 'siteurl' );

		$scheme = is_ssl() ? 'https' : parse_url( $url, PHP_URL_SCHEME );

		$url = set_url_scheme( $url, $scheme );

		return trailingslashit( $url );
	}

	/**
	 * Get WordPress uploads dir
	 *
	 * @since  1.0.0
	 *
	 * @return string Path to the uploads dir.
	 */
	public static function get_uploads_dir() {
		// Get the uploads dir.
		$upload_dir = wp_upload_dir();

		$base_dir = $upload_dir['basedir'];

		if ( defined( 'UPLOADS' ) ) {
			$base_dir = ABSPATH . UPLOADS;
		}

		return $base_dir;
	}

	/**
	 * Check for any updates available.
	 *
	 * @since  1.0.0
	 *
	 * @return boolean True if we have, false otherwise.
	 */
	public static function has_updates() {
		// Get dependencies.
		require_once ABSPATH . 'wp-admin/includes/update.php';

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check for theme updates.
		if ( ! empty( get_theme_updates() ) ) {
			return true;
		}

		// Check for plugin updates.
		if ( ! empty( get_plugin_updates() ) ) {
			return true;
		}

		// Check for translation updates.
		if ( ! empty( wp_get_translation_updates() ) ) {
			return true;
		}

		$core = get_core_updates();

		// Check for core.
		if ( 'upgrade' === $core[0]->response ) {
			return true;
		}

		// Bail if we do not have any updates available.
		return false;
	}

	/**
	 * Checks if the plugin run on the new SiteGround interface.
	 *
	 * @since  1.0.0
	 *
	 * @return boolean True/False.
	 */
	public static function is_siteground() {
		// Bail if open_basedir restrictions are set, and we are not able to check certain directories.
		if ( ! empty( ini_get( 'open_basedir' ) ) ){
			return 0;
		}

		return (int) ( @file_exists( '/etc/yum.repos.d/baseos.repo' ) && @file_exists( '/Z' ) );
	}
}
