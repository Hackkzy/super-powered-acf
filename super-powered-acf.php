<?php
/**
 * Plugin Name:       Super Powered ACF
 * Description:       A powerful AI based Field generator for ACF.
 * Version:           1.0.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            Hackkzy
 * Author URI:        https://github.com/Hackkzy
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       super-powered-acf
 *
 * @package Super_Powered_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Defining Constants.
 *
 * @package Super_Powered_ACF
 */
if ( ! defined( 'SUP_ACF_VERSION' ) ) {
	/**
	 * The version of the plugin.
	 */
	define( 'SUP_ACF_VERSION', '1.0.0' );
}

if ( ! defined( 'SUP_ACF_PATH' ) ) {
	/**
	 *  The server file system path to the plugin directory.
	 */
	define( 'SUP_ACF_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SUP_ACF_URL' ) ) {
	/**
	 * The url to the plugin directory.
	 */
	define( 'SUP_ACF_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SUP_ACF_BASE_NAME' ) ) {
	/**
	 * The url to the plugin directory.
	 */
	define( 'SUP_ACF_BASE_NAME', plugin_basename( __FILE__ ) );
}

// Apply translation file as per WP language.
if ( ! function_exists( 'sup_acf_text_domain_loader' ) ) :
	/**
	 * Apply translation file as per WP language.
	 */
	function sup_acf_text_domain_loader() {

		// Get mo file as per current locale.
		$mofile = SUP_ACF_PATH . 'languages/' . get_locale() . '.mo';

		// If file does not exists, then apply default mo.
		if ( ! file_exists( $mofile ) ) {
			$mofile = SUP_ACF_PATH . 'languages/default.mo';
		}

		load_textdomain( 'super-powered-acf', $mofile );
	}
endif;
add_action( 'plugins_loaded', 'sup_acf_text_domain_loader' );

/**
 * Check if ACF plugin is active and display notice if not.
 *
 * @return void
 */
function sup_acf_check_acf_dependency() {
	if ( ! class_exists( 'ACF' ) ) {
		add_action(
			'admin_notices',
			function () {
				printf(
					'<div class="notice notice-error is-dismissible">
                        <p><strong>%s</strong> %s <a href="%s" target="_blank">%s</a> %s <a href="%s" target="_blank">%s</a>. %s</p>
                    </div>',
					esc_html__( 'Super Powered ACF:', 'super-powered-acf' ),
					esc_html__( 'This plugin requires', 'super-powered-acf' ),
					esc_url( 'https://www.advancedcustomfields.com/' ),
					esc_html__( 'Advanced Custom Fields', 'super-powered-acf' ),
					esc_html__( 'or', 'super-powered-acf' ),
					esc_url( 'https://www.advancedcustomfields.com/pro/' ),
					esc_html__( 'Advanced Custom Fields Pro', 'super-powered-acf' ),
					esc_html__( 'Please install and activate ACF.', 'super-powered-acf' )
				);
			}
		);
	}
}
add_action( 'admin_init', 'sup_acf_check_acf_dependency' );

if ( class_exists( 'ACF' ) ) {
	require trailingslashit( SUP_ACF_PATH ) . 'app/includes/common-functions.php';
	require trailingslashit( SUP_ACF_PATH ) . 'app/admin/class-sup-acf-admin.php';
	require trailingslashit( SUP_ACF_PATH ) . 'app/admin/class-sup-acf-settings.php';
	require trailingslashit( SUP_ACF_PATH ) . 'app/api/v1/class-sup-acf-generate-fields.php';
}
