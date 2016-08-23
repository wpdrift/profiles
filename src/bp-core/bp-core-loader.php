<?php
/**
 * Profiles Core Loader.
 *
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package Profiles
 * @subpackage Core
 * @since 0.0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-bp-component.php';
	require dirname( __FILE__ ) . '/classes/class-bp-core.php';
}

/**
 * Set up the Profiles Core component.
 *
 * @since 0.0.1
 *
 * @global Profiles $bp Profiles global settings object.
 */
function bp_setup_core() {
	profiles()->core = new BP_Core();
}
add_action( 'bp_loaded', 'bp_setup_core', 0 );
