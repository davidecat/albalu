<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;

/**
 * Handles Facebook Conversion API (CAPI) server-side tracking.
 *
 * Listens for the `adt_pfp_after_pixel_event` action fired by PRO's
 * Facebook_Pixel class and sends the same event data to Facebook's
 * Conversions API endpoint.
 *
 * @since 5.0.7
 */
class Facebook_CAPI extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Facebook Conversions API access token.
     *
     * @since 5.0.7
     * @access protected
     *
     * @var string
     */
    protected $capi_token;

    /**
     * Handle the pixel event by sending it to the Conversions API.
     *
     * @since 5.0.7
     * @access public
     *
     * @param array|null $event    Structured event array from PRO's resolve_page_event(), or null.
     * @param string     $event_id Unique event ID for deduplication.
     * @param string     $pixel_id The Facebook Pixel ID.
     * @return void
     */
    public function handle_pixel_event( $event, $event_id, $pixel_id ) {
        $add_facebook_capi = get_option( 'adt_enable_facebook_capi' );
        $this->capi_token  = get_option( 'adt_facebook_capi_token' );

        if ( 'yes' !== $add_facebook_capi || empty( $this->capi_token ) ) {
            return;
        }

        $fb_capi_data = $this->build_base_capi_data( $event_id );

        if ( $event ) {
            $fb_capi_data['event_name']  = $event['event_name'];
            $fb_capi_data['custom_data'] = $this->build_capi_custom_data( $event );
        } else {
            $fb_capi_data['event_name'] = 'ViewContent';
        }

        $this->send_capi_event( $fb_capi_data, $pixel_id );
    }

    /**
     * Build the base CAPI data envelope (user data, timestamps, etc).
     *
     * The returned `user_data` array always contains `client_ip_address` and
     * `client_user_agent`, and additionally includes `fbc` / `fbp` when the
     * corresponding Meta cookies are present and non-empty.
     *
     * @since 5.0.7
     * @since 5.0.8 Forward Meta's `_fbc` / `_fbp` cookies into `user_data` when present.
     * @access protected
     *
     * @param string $event_id Unique event ID for deduplication.
     * @return array Base CAPI data array.
     */
    protected function build_base_capi_data( $event_id ) {
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
            ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
            : 'Unknown';

        $user_data = array(
            'client_ip_address' => \WC_Geolocation::get_ip_address(),
            'client_user_agent' => $user_agent,
        );

        // Forward Meta's _fbc (Click ID) and _fbp (Browser ID) cookies when present
        // and non-empty. These are written by Meta's fbevents.js pixel and are
        // high-value matching signals that improve Event Match Quality (EMQ) for
        // server-side CAPI events. Empty or absent cookies are omitted so we never
        // send empty matching parameters to Meta.
        $fbc = isset( $_COOKIE['_fbc'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['_fbc'] ) ) : '';
        if ( '' !== $fbc ) {
            $user_data['fbc'] = $fbc;
        }

        $fbp = isset( $_COOKIE['_fbp'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['_fbp'] ) ) : '';
        if ( '' !== $fbp ) {
            $user_data['fbp'] = $fbp;
        }

        return array(
            'match_keys'       => array(),
            'event_time'       => time(),
            'event_id'         => $event_id,
            'user_data'        => $user_data,
            'action_source'    => 'website',
            'event_source_url' => isset( $_SERVER['REQUEST_URI'] )
                ? home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) )
                : home_url( '/' ),
        );
    }

    /**
     * Map a structured event array to CAPI custom_data format.
     *
     * @since 5.0.7
     * @access protected
     *
     * @param array $event Structured event array from PRO's resolve_page_event().
     * @return array CAPI custom_data array.
     */
    protected function build_capi_custom_data( array $event ) {
        $event_data  = $event['event_data'] ?? array();
        $custom_data = array();

        // Map common fields directly from event_data.
        foreach ( array( 'currency', 'value', 'content_type', 'content_name', 'content_category', 'contents' ) as $key ) {
            if ( isset( $event_data[ $key ] ) ) {
                $custom_data[ $key ] = $event_data[ $key ];
            }
        }

        // content_ids: use CAPI-specific field when available, fall back to event_data.
        if ( isset( $event['product_id'] ) ) {
            $custom_data['content_ids'] = $event['product_id'];
        } elseif ( isset( $event['content_ids'] ) ) {
            $custom_data['content_ids'] = $event['content_ids'];
        } elseif ( isset( $event_data['content_ids'] ) ) {
            $custom_data['content_ids'] = $event_data['content_ids'];
        }

        // product_name for cart events (not in event_data but needed for CAPI).
        if ( isset( $event['product_name'] ) && ! isset( $custom_data['content_name'] ) ) {
            $custom_data['content_name'] = $event['product_name'];
        }

        return $custom_data;
    }

    /**
     * Send a server-side event to the Facebook Conversions API.
     *
     * @since 5.0.7
     * @access protected
     *
     * @param array  $fb_capi_data The event payload to send.
     * @param string $pixel_id     The Facebook Pixel ID.
     * @return void
     */
    protected function send_capi_event( array $fb_capi_data, $pixel_id ) {
        $fields = array(
            'access_token' => $this->capi_token,
            'upload_tag'   => $fb_capi_data['event_name'] . '-' . time(),
            'data'         => wp_json_encode( array( $fb_capi_data ) ),
        );

        $url = 'https://graph.facebook.com/v25.0/' . rawurlencode( $pixel_id ) . '/events';

        wp_remote_post(
            $url,
            array(
                'timeout'     => 30,
                'redirection' => 10,
                'httpversion' => '1.0',
                'blocking'    => false,
                'headers'     => array(
                    'cache-control' => 'no-cache',
                    'Accept'        => 'application/json',
                ),
                'body'        => $fields,
                'cookies'     => array(),
            )
        );
    }

    /**
     * Run the class.
     *
     * @since 5.0.7
     */
    public function run() {
        add_action( 'adt_pfp_after_pixel_event', array( $this, 'handle_pixel_event' ), 10, 3 );
    }
}
