<?php
/**
 * Profiles Members Actions.
 *
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 *
 * @package Profiles
 * @suprofilesackage MembersActions
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Catch a "Mark as Spammer/Not Spammer" click from the toolbar.
 *
 * When a site admin selects "Mark as Spammer/Not Spammer" from the admin menu
 * this action will fire and mark or unmark the user and their blogs as spam.
 * Must be a site admin for this function to run.
 *
 * Note: no longer used in the current state. See the Settings component.
 *
 * @since 1.1.0
 *
 * @param int $user_id Optional. User ID to mark as spam. Defaults to displayed user.
 */
function profiles_core_action_set_spammer_status( $user_id = 0 ) {

	// Only super admins can currently spam users (but they can't spam
	// themselves).
	if ( ! is_super_admin() || profiles_is_my_profile() ) {
		return;
	}

	// Use displayed user if it's not yourself.
	if ( empty( $user_id ) )
		$user_id = profiles_displayed_user_id();

	if ( profiles_is_current_component( 'admin' ) && ( in_array( profiles_current_action(), array( 'mark-spammer', 'unmark-spammer' ) ) ) ) {

		// Check the nonce.
		check_admin_referer( 'mark-unmark-spammer' );

		// To spam or not to spam.
		$status = profiles_is_current_action( 'mark-spammer' ) ? 'spam' : 'ham';

		// The heavy lifting.
		profiles_core_process_spammer_status( $user_id, $status );

		// Add feedback message. @todo - Error reporting.
		if ( 'spam' == $status ) {
			profiles_core_add_message( __( 'User marked as spammer. Spam users are visible only to site admins.', 'profiles' ) );
		} else {
			profiles_core_add_message( __( 'User removed as spammer.', 'profiles' ) );
		}

		// Deprecated. Use profiles_core_process_spammer_status.
		$is_spam = 'spam' == $status;
		do_action( 'profiles_core_action_set_spammer_status', profiles_displayed_user_id(), $is_spam );

		// Redirect back to where we came from.
		profiles_core_redirect( wp_get_referer() );
	}
}

/*
 * Unhooked in 1.6.0 - moved to settings.
 * add_action( 'profiles_actions', 'profiles_core_action_set_spammer_status' );
 */

/**
 * Process user deletion requests.
 *
 * Note: No longer called here. See the Settings component.
 *
 * @since 1.1.0
 */
function profiles_core_action_delete_user() {

	if ( !profiles_current_user_can( 'profiles_moderate' ) || profiles_is_my_profile() || !profiles_displayed_user_id() )
		return false;

	if ( profiles_is_current_component( 'admin' ) && profiles_is_current_action( 'delete-user' ) ) {

		// Check the nonce.
		check_admin_referer( 'delete-user' );

		$errors = false;
		do_action( 'profiles_core_before_action_delete_user', $errors );

		if ( profiles_core_delete_account( profiles_displayed_user_id() ) ) {
			profiles_core_add_message( sprintf( __( '%s has been deleted from the system.', 'profiles' ), profiles_get_displayed_user_fullname() ) );
		} else {
			profiles_core_add_message( sprintf( __( 'There was an error deleting %s from the system. Please try again.', 'profiles' ), profiles_get_displayed_user_fullname() ), 'error' );
			$errors = true;
		}

		do_action( 'profiles_core_action_delete_user', $errors );

		if ( $errors )
			profiles_core_redirect( profiles_displayed_user_domain() );
		else
			profiles_core_redirect( profiles_loggedin_user_domain() );
	}
}

/*
 * Unhooked in 1.6.0 - moved to settings
 * add_action( 'profiles_actions', 'profiles_core_action_delete_user' );
 */

/**
 * Redirect to a random member page when visiting a ?random-member URL.
 *
 * @since 1.0.0
 */
function profiles_core_get_random_member() {
	if ( ! isset( $_GET['random-member'] ) )
		return;

	$user = profiles_core_get_users( array( 'type' => 'random', 'per_page' => 1 ) );
	profiles_core_redirect( profiles_core_get_user_domain( $user['users'][0]->id ) );
}
add_action( 'profiles_actions', 'profiles_core_get_random_member' );
