<?php
/**
 * Profiles Core Loader.
 *
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package Profiles
 * @suprofilesackage Core
 * @since 0.0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-component.php';
	require dirname( __FILE__ ) . '/classes/class-profiles-core.php';
}

/**
 * Set up the Profiles Core component.
 *
 * @since 0.0.1
 *
 * @global Profiles $profiles Profiles global settings object.
 */
function profiles_setup_core() {
	profiles()->core = new Profiles_Core();
}
add_action( 'profiles_loaded', 'profiles_setup_core', 0 );
