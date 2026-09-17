<?php
/**
 * Functions for logging
 * @since 3.0.0
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pewc_error_log( $message ) {
	// Have we got a log file yet?
	$pewc_log_file = get_option( 'pewc_log_file', false );
	if( ! $pewc_log_file ) {
		$pewc_log_file = md5( time() );
		update_option( 'pewc_log_file', $pewc_log_file );
	}
	$file = trailingslashit( PEWC_PLUGIN_DIR_PATH ). "logs/" . $pewc_log_file . ".log";
	if( ! file_exists( $file ) ) {
		$handle = fopen( $file, "a" );
	}
	$current = file_get_contents( $file );
	$current .= date( 'Y-m-d h:i:s' ) . ": " . $message . "\n";
	file_put_contents( $file, $current );
}

function pewc_get_runtime_context() {
	$env = get_option( 'pewc_env_state', array() );
	if( ! is_array( $env ) || empty( $env['scope'] ) ) {
		return '';
	}
	return (string) $env['scope'];
}

function pewc_get_runtime_context_since() {
	$env = get_option( 'pewc_env_state', array() );
	if( ! is_array( $env ) || empty( $env['since'] ) ) {
		return 0;
	}
	return (int) $env['since'];
}

function pewc_schedule_runtime_sync() {
	if( ! wp_next_scheduled( 'pewc_runtime_sync' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'pewc_runtime_sync' );
	}
}
add_action( 'init', 'pewc_schedule_runtime_sync' );

function pewc_do_runtime_sync() {
	if( ! function_exists( 'pewc_do_license_activation' ) ) {
		return;
	}
	$license = defined( 'PEWC_LICENSE_KEY' ) ? trim( PEWC_LICENSE_KEY ) : trim( get_option( 'pewc_license_key' ) );
	if( $license ) {
		pewc_do_license_activation( $license );
	}
}
add_action( 'pewc_runtime_sync', 'pewc_do_runtime_sync' );

function pewc_unschedule_runtime_sync() {
	wp_clear_scheduled_hook( 'pewc_runtime_sync' );
}
register_deactivation_hook( PEWC_FILE, 'pewc_unschedule_runtime_sync' );
