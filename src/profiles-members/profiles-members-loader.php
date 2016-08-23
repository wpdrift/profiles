<?php
/**
 * Profiles Member Loader.
 *
 * @package Profiles
 * @suprofilesackage Members
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-members-component.php';
}

/**
 * Set up the profiles-members component.
 *
 * @since 1.6.0
 */
function profiles_setup_members() {
	profiles()->members = new Profiles_Members_Component();
}
add_action( 'profiles_setup_components', 'profiles_setup_members', 1 );
