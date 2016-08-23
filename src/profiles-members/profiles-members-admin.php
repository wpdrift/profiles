<?php
/**
 * Profiles Members Admin
 *
 * @package Profiles
 * @suprofilesackage MembersAdmin
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-members-admin.php';
}

// Load the BP Members admin.
add_action( 'profiles_init', array( 'Profiles_Members_Admin', 'register_members_admin' ) );
