<?php
/**
 * Class for admin methods.
 *
 * @package Super_Powered_ACF
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'Sup_ACF_Admin' ) ) {

	/**
	 * Calls for admin methods.
	 */
	class Sup_ACF_Admin {

		/**
		 * Constructor for class.
		 */
		public function __construct() {

			// Enqueue custom scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'sup_acf_enqueue_scripts' ) );
		}

		/**
		 * Enqueue custom admin scripts.
		 *
		 * @param string $hook The current admin page.
		 * @return void
		 */
		public function sup_acf_enqueue_scripts( $hook ) {

			if ( 'post-new.php' !== $hook || 'acf-field-group' !== get_post_type() ) {
				return;
			}

			$plugin_asset = 'super-powered-acf-admin';

			if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
				// If Script debug disabled then include minified files.
				$plugin_asset .= '.min';
			}

			// Plugin Related Style and Scripts.
			wp_enqueue_script(
				'super-powered-acf-admin',
				trailingslashit( SUP_ACF_URL ) . 'app/admin/assets/js/' . $plugin_asset . '.js',
				array( 'jquery', 'acf-input' ),
				SUP_ACF_VERSION,
				true
			);

			/**
			 * Filter the localized script data
			 *
			 * @param array $data Array of data to be localized
			 */
			$localized_data = apply_filters(
				'sup_acf_localized_data',
				array(
					'root'  => esc_url_raw( rest_url() ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
					'i18n'  => array(
						'modalTitle'        => esc_html__( 'Generate ACF Fields with AI', 'super-powered-acf' ),
						'promptLabel'       => esc_html__( 'Enter your prompt:', 'super-powered-acf' ),
						'promptPlaceholder' => esc_html__( 'Describe the fields you want to generate...', 'super-powered-acf' ),
						'promptHelp'        => esc_html__( 'Describe the fields you want to create. Be as specific as possible about field types and requirements.', 'super-powered-acf' ),
						'aiNote'            => esc_html__( 'Note: AI-generated results may vary. You might need to try different prompts or regenerate fields to get the desired outcome.', 'super-powered-acf' ),
						'generateFields'    => esc_html__( 'Generate Fields', 'super-powered-acf' ),
						'generatingFields'  => esc_html__( 'Generating Fields...', 'super-powered-acf' ),
						'generateWithAI'    => esc_html__( 'Generate Fields with AI', 'super-powered-acf' ),
						'enterPrompt'       => esc_html__( 'Please enter a prompt to generate fields.', 'super-powered-acf' ),
						'noFieldsGenerated' => esc_html__( 'No fields were generated. Please try a different prompt.', 'super-powered-acf' ),
						'genericError'      => esc_html__( 'An error occurred while generating fields. Please try again.', 'super-powered-acf' ),
					),
				)
			);

			wp_localize_script( 'super-powered-acf-admin', 'sup_acf', $localized_data );

			wp_enqueue_style(
				'super-powered-acf-admin',
				trailingslashit( SUP_ACF_URL ) . 'app/admin/assets/css/' . $plugin_asset . '.css',
				array(),
				SUP_ACF_VERSION
			);

			/**
			 * Action after scripts and styles are enqueued
			 *
			 * @param string $hook The current admin page hook
			 */
			do_action( 'sup_acf_after_enqueue_scripts', $hook );
		}
	}
	new Sup_ACF_Admin();
}
