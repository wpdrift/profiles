<?php
/**
 * Roles and capabilities logic for the XProfile component.
 *
 * @package Profiles
 * @suprofilesackage XPRofileCaps
 * @since 1.6.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Maps XProfile caps to built in WordPress caps.
 *
 * @since 1.6.0
 *
 * @param array  $caps    Capabilities for meta capability.
 * @param string $cap     Capability name.
 * @param int    $user_id User id.
 * @param mixed  $args    Arguments.
 *
 * @return array Actual capabilities for meta capability.
 */
function profiles_xprofile_map_meta_caps( $caps, $cap, $user_id, $args ) {
	switch ( $cap ) {
		case 'profiles_xprofile_change_field_visibility' :
			$caps = array( 'exist' ); // Must allow for logged-out users during registration.

			// You may pass args manually: $field_id, $profile_user_id.
			$field_id        = isset( $args[0] ) ? (int)$args[0] : profiles_get_the_profile_field_id();
			$profile_user_id = isset( $args[1] ) ? (int)$args[1] : profiles_displayed_user_id();

			// Visibility on the fullname field is not editable.
			if ( 1 == $field_id ) {
				$caps[] = 'do_not_allow';
				break;
			}

			// Has the admin disabled visibility modification for this field?
			if ( 'disabled' == profiles_xprofile_get_meta( $field_id, 'field', 'allow_custom_visibility' ) ) {
				$caps[] = 'do_not_allow';
				break;
			}

			// Friends don't let friends edit each other's visibility.
			if ( $profile_user_id != profiles_displayed_user_id() && !profiles_current_user_can( 'profiles_moderate' ) ) {
				$caps[] = 'do_not_allow';
				break;
			}

			break;
	}

	/**
	 * Filters the XProfile caps to built in WordPress caps.
	 *
	 * @since 1.6.0
	 *
	 * @param array  $caps    Capabilities for meta capability.
	 * @param string $cap     Capability name.
	 * @param int    $user_id User ID being mapped.
	 * @param mixed  $args    Capability arguments.
	 */
	return apply_filters( 'profiles_xprofile_map_meta_caps', $caps, $cap, $user_id, $args );
}
add_filter( 'profiles_map_meta_caps', 'profiles_xprofile_map_meta_caps', 10, 4 );
