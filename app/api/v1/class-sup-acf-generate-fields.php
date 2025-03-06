<?php
/**
 * REST Controller - Super Powered ACF Generate Fields.
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
if ( ! class_exists( 'Sup_ACF_Generate_Fields' ) ) {
	/**
	 * Sup_ACF_Generate_Fields class.
	 */
	class Sup_ACF_Generate_Fields extends WP_REST_Controller {

		/**
		 * Plugin settings
		 *
		 * @var array
		 */
		private $sup_acf_settings;

		/**
		 * Here initialize our namespace and resource name.
		 */
		public function __construct() {
			$this->namespace        = '/sup-acf/v1';
			$this->route            = '/generate-fields';
			$this->sup_acf_settings = get_option( 'sup_acf_options' );
		}

		/**
		 * Register our routes.
		 *
		 * @return void
		 */
		public function register_routes() {
			register_rest_route(
				$this->namespace,
				$this->route,
				array(
					// Here we register the readable endpoint for collections.
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => array(
							'prompt' => array(
								'required'          => false,
								'sanitize_callback' => 'sanitize_text_field',
								'validate_callback' => function ( $param, $request, $key ) {
									return is_string( $param );
								},
								'description'       => esc_html__( 'Prompt for AI to generate ACF fields.', 'super-powered-acf' ),
							),
						),
					),
				)
			);
		}

		/**
		 * Grabs the five most recent posts and outputs them as a rest response.
		 *
		 * @param WP_REST_Request $request Current request.
		 */
		public function get_items( $request ) {

			$prompt = apply_filters( 'sup_acf_pre_prompt', $request->get_param( 'prompt' ) );

			if ( ! is_string( $prompt ) || empty( trim( $prompt ) ) ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'message' => esc_html__( 'Prompt is required. Please provide a valid prompt for AI to generate ACF fields.', 'super-powered-acf' ),
					),
					400
				);
			}

			$ai_response = $this->fetch_ai_generated_fields( $prompt );

			if ( is_wp_error( $ai_response ) ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'message' => $ai_response->get_error_message(),
					),
					400
				);
			}

			$ai_response = apply_filters( 'sup_acf_post_response', $ai_response );

			// Return response data.
			return new WP_REST_Response(
				array(
					'success' => true,
					'fields'  => $ai_response,
				),
				200
			);
		}

		/**
		 * Generate Fields from AI.
		 *
		 * @param string $prompt AI Prompt.
		 * @return WP_Error|array WP_Error on failure, Array of ACF fields on success.
		 */
		public function fetch_ai_generated_fields( $prompt ) {

			$api_key = ! empty( $this->sup_acf_settings['sup_acf_gemini_key'] ) ? $this->sup_acf_settings['sup_acf_gemini_key'] : '';

			if ( empty( $api_key ) ) {
				return new WP_Error(
					'api_key_missing',
					esc_html__( 'Gemini API key is missing.', 'super-powered-acf' )
				);
			}

			$api_url = add_query_arg(
				'key',
				$api_key,
				'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent'
			);

			$system_prompt = 'You are an expert ACF (Advanced Custom Fields) field generator. Your sole purpose is to generate ACF field definitions in JSON format. You will be given descriptions of the desired fields and MUST output the corresponding ACF field in JSON format.*   **Output Format:** Your responses MUST be valid JSON, formatted as a JSON array of JSON objects. Each JSON object represents a single ACF field. The output should ALWAYS be enclosed in `[` and `]`.  Do NOT include any extraneous text, explanations, or commentary. Only valid JSON.*   **Field Keys:** Generate unique, lowercase, alphanumeric field keys, prefixed with `field_` (e.g., `field_my_field`). Generate a random set of numbers and/or characters if I don\'t provide one.*   **Field Names:** Use lowercase, alphanumeric field names with underscores (e.g., `my_field`).*   **Field Types:** Use accurate ACF field types (e.g., \'text\', \'textarea\', \'image\', \'select\', \'wysiwyg\', \'number\', \'true_false\', etc.).*   **Required:** Use 1 for required, 0 for not required.*   **Instructions:** Keep instructions concise and clear.*   **Settings:** Include all necessary and relevant settings for the field type (e.g., `choices` for a \'select\' field, `return_format` for an \'image\' field, `default_value` for a text field).*   **Conditional Logic:** If conditional logic is specified, include the `conditional_logic` key with its settings. If not specified, do NOT include `conditional_logic` key.*   **Wrapper:** If the \'width\', \'class\' or \'id\' of the field is mentioned, include the `wrapper` key with its settings. If not specified, do NOT include the `wrapper` key.*   **Multiple Fields:** If I ask for more than one field, include each field as a separate object within the JSON array.*   **Error Handling:** If you cannot generate an ACF field according to my instructions (e.g., due to ambiguous requests, missing information about field type, or insufficient detail for settings), output the following: `[{"error": "Please ensure your prompt is related to generating ACF fields. I require specific details about the field\'s label, name, and type. Provide necessary settings if applicable."}]`.* YOU CAN ONLY GENERATE ACF FIELDS, IF THE PROMPT DOES NOT RELATE TO ACF FIELDS, RESPOND WITH ERROR MESSAGE';
			$system_prompt = apply_filters( 'sup_acf_system_prompt', $system_prompt );

			$request_body = array(
				'contents'          => array(
					array(
						'role'  => 'user',
						'parts' => array(
							array(
								'text' => $prompt,
							),
						),
					),
				),
				'systemInstruction' => array(
					'role'  => 'user',
					'parts' => array(
						array(
							'text' => $system_prompt,
						),
					),
				),
				'generationConfig'  => array(
					'responseMimeType' => 'application/json',
				),
			);

			$response = wp_remote_post(
				$api_url,
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
					),
					'body'    => wp_json_encode( $request_body ),
					'timeout' => 30,
				)
			);

			if ( is_wp_error( $response ) ) {
				return new WP_Error(
					'api_error',
					sprintf(
						/* translators: %s: Error message */
						esc_html__( 'API request failed: %s', 'super-powered-acf' ),
						$response->get_error_message()
					)
				);
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $response_code ) {
				return new WP_Error(
					'api_error',
					sprintf(
						/* translators: %d: HTTP response code */
						esc_html__( 'API request failed with status code: %d', 'super-powered-acf' ),
						$response_code
					)
				);
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
				return new WP_Error(
					'invalid_response',
					esc_html__( 'Invalid JSON response from API.', 'super-powered-acf' )
				);
			}

			if ( empty( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
				return new WP_Error(
					'empty_response',
					esc_html__( 'Empty response from API.', 'super-powered-acf' )
				);
			}

			$ai_text = $data['candidates'][0]['content']['parts'][0]['text'];

			// Decode the JSON string.
			$ai_fields = json_decode( $ai_text, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $ai_fields ) ) {
				return new WP_Error(
					'invalid_json',
					esc_html__( 'Invalid JSON response from AI. Please ensure your prompt is related to generating ACF fields.', 'super-powered-acf' )
				);
			}

			// Basic validation that we have an array of fields.
			if ( empty( $ai_fields ) || ! is_array( $ai_fields ) ) {
				return new WP_Error(
					'invalid_acf_fields',
					esc_html__( 'Invalid ACF field structure in AI response. No fields were generated.', 'super-powered-acf' )
				);
			}

			// Check if response contains an error message.
			if ( count( $ai_fields ) === 1 && isset( $ai_fields[0]['error'] ) ) {
				return new WP_Error(
					'ai_error',
					sprintf(
						/* translators: %s: Error message from AI */
						esc_html__( 'AI Response Error: %s', 'super-powered-acf' ),
						$ai_fields[0]['error']
					)
				);
			}

			return $ai_fields;
		}

		/**
		 * Check if a given request has access to get items
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function get_items_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}
	}

	// register our new routes.
	add_action(
		'rest_api_init',
		function () {
			$controller = new Sup_ACF_Generate_Fields();
			$controller->register_routes();
		}
	);
}
