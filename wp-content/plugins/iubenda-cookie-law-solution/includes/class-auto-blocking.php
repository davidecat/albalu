<?php
/**
 * Iubenda Auto Blocking Handler.
 *
 * Handles auto-blocking functionality for scripts.
 *
 * @package Iubenda
 */

/**
 * Handles the automatic blocking of scripts.
 */
class Auto_Blocking {
	/**
	 * Stores autoblocking options.
	 *
	 * @var array An array for storing autoblocking options.
	 */
	public $auto_block_sites_status = array();

	/**
	 * Instance of Iubenda_CS_Product_Service.
	 *
	 * @var Iubenda_CS_Product_Service An instance of the Iubenda_CS_Product_Service class.
	 */
	private $cs_product_service;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->cs_product_service      = new Iubenda_CS_Product_Service();
		$this->auto_block_sites_status = iub_array_get( iubenda()->options, 'cs.frontend_auto_blocking', array() );

		add_action( 'wp_ajax_check_frontend_auto_blocking_status', array( $this, 'check_frontend_auto_blocking_by_code' ) );
	}

	/**
	 * Parses the configuration from the provided script and returns the site ID.
	 *
	 * @param   string $script  The script to parse.
	 *
	 * @return string The site ID.
	 */
	public function get_site_id_from_cs_code( $script ) {
		return iubenda()->configuration_parser->retrieve_info_from_script_by_key( $script, 'siteId' );
	}

	/**
	 * Parses the configuration from the provided script and returns the Cookie Policy ID.
	 *
	 * @param   string $script  The script to parse.
	 *
	 * @return string The Cookie Policy ID.
	 */
	public function get_cookie_policy_id_from_cs_code( $script ) {
		return iubenda()->configuration_parser->retrieve_info_from_script_by_key( $script, 'cookiePolicyId' );
	}

	/**
	 * Parses the configuration from the provided script and returns the Google URL Passthrough setting.
	 *
	 * @param   string $script  The script to parse.
	 *
	 * @return bool|null The Google URL Passthrough setting, or null if not found.
	 */
	public function get_google_url_passthrough_from_cs_code( $script ) {
		$value = iubenda()->configuration_parser->retrieve_info_from_script_by_key( $script, 'googleUrlPassthrough' );
		if ( null === $value || '' === $value ) {
			return null;
		}
		return (bool) $value;
	}

	/**
	 * Parses the configuration from the provided script and returns the Google Ads Data Redaction setting.
	 *
	 * @param   string $script  The script to parse.
	 *
	 * @return bool|null The Google Ads Data Redaction setting, or null if not found.
	 */
	public function get_google_ads_data_redaction_from_cs_code( $script ) {
		$value = iubenda()->configuration_parser->retrieve_info_from_script_by_key( $script, 'googleAdsDataRedaction' );
		if ( null === $value || '' === $value ) {
			return null;
		}
		return (bool) $value;
	}

	/**
	 * Checks if the autoblocking feature is available for the given site ID and updates the status.
	 *
	 * @param   string $site_id  The site ID to check.
	 */
	public function fetch_auto_blocking_status_by_site_id( $site_id ) {
		$this->auto_block_sites_status[ $site_id ] = $this->is_autoblocking_feature_available( $site_id );
	}

	/**
	 * Checks whether the autoblocking feature is available for the given site ID.
	 *
	 * @param   string $site_id  The site ID to check.
	 *
	 * @return bool True if the autoblocking feature is available; otherwise, false.
	 */
	public function is_autoblocking_feature_available( $site_id ) {
		// Build the URL.
		$url = 'https://cs.iubenda.com/autoblocking/' . $site_id . '.js';

		// Set the timeout.
		$timeout = 5;

		// Configure the request parameters.
		$args = array(
			'timeout' => $timeout,
		);

		// Make a remote request.
		$remote_file = wp_remote_get( $url, $args );

		// Retrieve the response body from the remote request.
		$content = wp_remote_retrieve_body( $remote_file );

		// Check length of content must be more than 150 character.
		if ( 150 >= strlen( $content ) ) {
			// Content is too short, return false.
			return false;
		}

		// Check if the content contains the indicator for an unavailable feature.
		return false === strpos( $content, 'Autoblocking not enabled' );
	}

	/**
	 * Checks if the string "iubenda.com/autoblocking/" is present in the provided script.
	 *
	 * @param   string $script  The script to check.
	 *
	 * @return bool True if the string is present; otherwise, false.
	 */
	public function is_autoblocking_script_present( $script ) {
		$script = stripslashes( $script );

		// Check if the string "iubenda.com/autoblocking/" is present in the script.
		// The function returns true if the string is found and false otherwise.
		return false !== strpos( $script, 'iubenda.com/autoblocking/' );
	}

	/**
	 * Process a script and update Autoblocking options based on the provided CS code.
	 *
	 * @param string $script The script to process.
	 */
	public function process_autoblocking_code( $script ) {
		// Get site_id from embed code.
		$site_id_from_cs_code = $this->get_site_id_from_cs_code( $script );

		// Check if the status has already been updated for this site_id.
		if ( isset( $this->auto_block_sites_status[ $site_id_from_cs_code ] ) ) {
			return;
		}

		// Initialize the Autoblocking option for the site_id.
		$this->auto_block_sites_status[ $site_id_from_cs_code ] = false;

		// Check if site_id is empty, and if so, return early.
		if ( empty( $site_id_from_cs_code ) ) {
			return;
		}

		// Check if Autoblocking script is present in the option.
		if ( $this->is_autoblocking_script_present( $script ) ) {
			// Handle the status based on the site_id.
			$this->fetch_auto_blocking_status_by_site_id( $site_id_from_cs_code );
		} else {
			// If Autoblocking script is not present, set the option to false.
			$this->auto_block_sites_status[ $site_id_from_cs_code ] = false;
		}
	}

	/**
	 * Extracts the site ID from a script URL containing "autoblocking" and ending with ".js".
	 *
	 * @param string $script_url The URL of the script.
	 *
	 * @return string|null The autoblocking number if found, or null if no match is found.
	 */
	private function get_site_id_from_script_url( $script_url ) {
		$pattern = '/autoblocking\/(\d+)\.js/';

		if ( preg_match( $pattern, $script_url, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Checks if an autoblocking script should be attached based on its source URL.
	 *
	 * @param string $src The source URL of the script.
	 *
	 * @return bool Whether the autoblocking script should be attached or not.
	 */
	public function should_autoblocking_script_attached( $src ) {
		$site_id = $this->get_site_id_from_script_url( $src );

		if ( ! empty( $site_id ) ) {
			// Check if the autoblocking script should be attached based on the site ID.
			return $this->auto_block_sites_status[ $site_id ] ?? false;
		}

		return false;
	}

	/**
	 * Check the frontend auto-blocking status based on the provided code.
	 *
	 * This function is intended to be used as an AJAX callback for checking the auto-blocking status.
	 *
	 * @return void
	 */
	public function check_frontend_auto_blocking_by_code() {
		iub_verify_ajax_request( 'check_frontend_auto_blocking_status', 'iub_nonce' );
		$site_id            = '';
		$configuration_type = iub_get_request_parameter( 'configuration_type' );
		$code               = iub_get_request_parameter( 'code', null, false );
		if ( ! $code ) {
			wp_send_json( false );
		}

		// Check if the code uses the new unified format (embeds.iubenda.com/widgets/).
		if ( $this->is_unified_embed_format( $code ) ) {
			// Verify if autoblocking is actually enabled for this unified embed.
			wp_send_json( $this->is_unified_autoblocking_enabled( $code ) );
		}

		// Check if the configuration_type is simplified.
		if ( 'simplified' === $configuration_type ) {
			$site_id = iub_array_get( iubenda()->options['global_options'], 'site_id' );
		} elseif ( 'manual' === $configuration_type ) {
			// Get site_id from embed code.
			$site_id = $this->get_site_id_from_cs_code( $code );
		}

		if ( ! $site_id ) {
			wp_send_json( false );
		}

		wp_send_json( $this->is_autoblocking_feature_available( $site_id ) );
	}

	/**
	 * Check if autoblocking is enabled for a unified embed URL.
	 * Extracts the site_id from the embed URL and checks autoblocking availability.
	 *
	 * @param   string $code  embed code containing unified embed URL.
	 *
	 * @return bool True if autoblocking is enabled for this embed URL, false otherwise.
	 */
	public function is_unified_autoblocking_enabled( $code ) {
		// Strip slashes to handle escaped quotes.
		$code = stripslashes( $code );

		// Reuse existing URL extraction methods from configuration_parser.
		$embed_url = iubenda()->configuration_parser->extract_embed_url_with_dom( $code );

		if ( false === $embed_url ) {
			// Fallback to string parsing.
			$embed_url = iubenda()->configuration_parser->extract_embed_url_with_fallback( $code );
		}

		if ( false === $embed_url ) {
			return false;
		}

		// Fetch the JavaScript content from embed URL.
		$response = wp_remote_get( $embed_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$js_content = wp_remote_retrieve_body( $response );

		// Extract _iub.csSiteConf from the JavaScript.
		if ( ! preg_match( '/_iub\.csSiteConf\s*=\s*({[^;]+});/', $js_content, $config_matches ) ) {
			return false;
		}

		$config_json = $config_matches[1];
		$decoded     = json_decode( $config_json, true );

		if ( empty( $decoded ) || ! is_array( $decoded ) ) {
			return false;
		}

		// Extract siteId from the configuration.
		$site_id = isset( $decoded['siteId'] ) ? $decoded['siteId'] : null;

		if ( empty( $site_id ) ) {
			return false;
		}

		// Use existing function to check if autoblocking is available for this site_id.
		return $this->is_autoblocking_feature_available( $site_id );
	}

	/**
	 * Check if the embed code uses the new unified format (embeds.iubenda.com/widgets/).
	 * Supports both production and staging environments.
	 *
	 * @param   string $code  embed code.
	 *
	 * @return bool True if the code uses the unified embed format, false otherwise.
	 */
	public function is_unified_embed_format( $code ) {
		return false !== stripos( $code, 'embeds.iubenda.com/widgets/' );
	}
}
