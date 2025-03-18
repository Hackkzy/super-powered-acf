<?php
/**
 * Class Sup_ACF_Settings
 *
 * Configure the plugin settings page.
 *
 * @package Super_Powered_ACF
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Sup_ACF_Settings' ) ) {

	/**
	 * Plugin settings class.
	 */
	class Sup_ACF_Settings {

		/**
		 * Capability required to access the settings page.
		 *
		 * @var string $capability
		 */
		private $capability = 'manage_options';

		/**
		 * Settings Fields.
		 *
		 * @var array
		 */
		protected $fields = array();

		/**
		 * Constructor to initialize settings.
		 */
		public function __construct() {

			add_action( 'admin_menu', array( $this, 'sup_acf_add_settings_page' ) );
			add_action( 'admin_init', array( $this, 'sup_acf_settings_init' ) );
		}

		/**
		 * Add the settings page to the admin menu.
		 */
		public function sup_acf_add_settings_page() {
			add_menu_page(
				esc_html__( 'Super Powered ACF', 'super-powered-acf' ),
				'Super Powered ACF',
				$this->capability,
				'sup-acf-settings',
				array( $this, 'sup_acf_render_settings_page' ),
				'dashicons-superhero-alt'
			);
		}

		/**
		 * Render the settings page.
		 */
		public function sup_acf_render_settings_page() {
			if ( ! current_user_can( $this->capability ) ) {
				return;
			}

			settings_errors();
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<form action="options.php" method="post">
					<?php
					/**
					 * Fires before the Super Powered ACF settings form fields.
					 *
					 * @since 1.0.0
					 */
					do_action( 'sup_acf_before_settings_fields' );

					settings_fields( 'sup-acf-settings-group' );
					do_settings_sections( 'sup-acf-settings' );

					/**
					 * Fires after the Super Powered ACF settings form fields.
					 *
					 * @since 1.0.0
					 */
					do_action( 'sup_acf_after_settings_fields' );

					submit_button( esc_html__( 'Save Settings', 'super-powered-acf' ) );
					?>
				</form>
				<?php
				/**
				 * Fires after the Super Powered ACF settings form.
				 *
				 * @since 1.0.0
				 */
				do_action( 'sup_acf_after_settings_form' );
				?>
			</div>
			<?php
		}

		/**
		 * Register settings and fields.
		 */
		public function sup_acf_settings_init() {
			register_setting(
				'sup-acf-settings-group',
				'sup_acf_options',
				array(
					'sanitize_callback' => array( $this, 'sup_acf_sanitize_options' ),
				)
			);

			add_settings_section(
				'sup_acf_settings_section',
				esc_html__( 'General Settings', 'super-powered-acf' ),
				'__return_false',
				'sup-acf-settings'
			);

			$this->fields = array(
				array(
					'id'          => 'sup_acf_gemini_key',
					'label'       => esc_html__( 'Gemini API Key', 'super-powered-acf' ),
					'description' => esc_html__( 'Enter your Gemini API key.', 'super-powered-acf' ),
					'type'        => 'password',
				),
			);

			// Filter to allow modification of settings fields.
			$this->fields = apply_filters( 'sup_acf_settings_fields', $this->fields );

			foreach ( $this->fields as $field ) {
				add_settings_field(
					$field['id'],
					esc_html( $field['label'] ),
					array( $this, 'sup_acf_render_field' ),
					'sup-acf-settings',
					'sup_acf_settings_section',
					array( 'field' => $field )
				);
			}
		}

		/**
		 * Render a settings field.
		 *
		 * @param array $args Args to configure the field.
		 */
		public function sup_acf_render_field( $args ) {

			$field       = $args['field'];
			$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';

			// Get the value of the setting we've registered with register_setting().
			$options = get_option( 'sup_acf_options', array() );

			switch ( $field['type'] ) {
				case 'select':
				case 'multiselect':
					if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
						$multiple       = '';
						$select_options = '';
						foreach ( $field['options'] as $key => $label ) {
							$selected_val    = ! empty( $options[ $field['id'] ] ) ? $options[ $field['id'] ] : '';
							$select_options .= sprintf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $key ),
								selected( $selected_val, $key, false ),
								esc_html( $label )
							);
						}
						if ( 'type' === $field['multiselect'] ) {
							$multiple = 'multiple';
						}
						printf(
							'<select name="sup_acf_options[%1$s]" id="%1$s" %2$s>%3$s</select>',
							esc_attr( $field['id'] ),
							esc_attr( $multiple ),
							$select_options //phpcs:ignore
						);
					}
					break;
				default:
					printf(
						'<input name="sup_acf_options[%1$s]" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
						esc_attr( $field['id'] ),
						esc_attr( $field['type'] ),
						esc_attr( $placeholder ),
						! empty( $options[ $field['id'] ] ) ? esc_attr( $options[ $field['id'] ] ) : ''
					);
			}

			$description = ! empty( $field['description'] ) ? $field['description'] : '';

			if ( ! empty( $description ) ) {
				printf( '<p class="description">%s </p>', esc_html( $description ) );
			}
		}

		/**
		 * Sanitize options dynamically based on field type.
		 *
		 * @param array $input The input array containing form values to sanitize.
		 * @return array The sanitized input array.
		 */
		public function sup_acf_sanitize_options( $input ) {
			$fields = $this->fields;

			if ( empty( $fields ) || ! is_array( $fields ) ) {
				return $input;
			}

			foreach ( $fields as $field ) {

				$input[ $field['id'] ] = isset( $input[ $field['id'] ] ) ? $input[ $field['id'] ] : '';

				// Sanitize based on field type.
				switch ( $field['type'] ) {
					case 'text':
					case 'password':
						$input[ $field['id'] ] = sanitize_text_field( $input[ $field['id'] ] );
						break;

					case 'email':
						$input[ $field['id'] ] = sanitize_email( $input[ $field['id'] ] );
						break;

					case 'url':
						$input[ $field['id'] ] = esc_url_raw( $input[ $field['id'] ] );
						break;

					case 'number':
						$input[ $field['id'] ] = is_numeric( $input[ $field['id'] ] ) ? intval( $input[ $field['id'] ] ) : 0;
						break;

					case 'textarea':
						$input[ $field['id'] ] = sanitize_textarea_field( $input[ $field['id'] ] );
						break;

					case 'checkbox':
						$input[ $field['id'] ] = ( '1' === $input[ $field['id'] ] ) ? '1' : '0';
						break;

					case 'select':
					case 'radio':
						$input[ $field['id'] ] = sanitize_text_field( $input[ $field['id'] ] );
						break;

					case 'multiselect':
						if ( is_array( $input[ $field['id'] ] ) ) {
							$input[ $field['id'] ] = array_map( 'sanitize_text_field', $input[ $field['id'] ] );
						} else {
							$input[ $field['id'] ] = array();
						}
						break;

					default:
						$input[ $field['id'] ] = sanitize_text_field( $input[ $field['id'] ] );
						break;
				}
			}

			return apply_filters( 'sup_acf_sanitized_options', $input, $fields );
		}
	}
	new Sup_ACF_Settings();
}
