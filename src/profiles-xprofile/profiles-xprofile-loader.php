<?php
/**
 * Profiles XProfile Loader.
 *
 * An extended profile component for users. This allows site admins to create
 * groups of fields for users to enter information about themselves.
 *
 * @package Profiles
 * @suprofilesackage XProfileLoader
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-xprofile-component.php';
}

/**
 * Bootstrap the XProfile component.
 *
 * @since 1.6.0
 */
function profiles_setup_xprofile() {
	$profiles = profiles();

	if ( ! isset( $profiles->profile->id ) ) {
		$profiles->profile = new Profiles_XProfile_Component();
	}
}
add_action( 'profiles_setup_components', 'profiles_setup_xprofile', 2 );
