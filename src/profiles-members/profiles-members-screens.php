<?php
/**
 * Profiles Member Screens.
 *
 * Handlers for member screens that aren't handled elsewhere.
 *
 * @package Profiles
 * @suprofilesackage MembersScreens
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-members-theme-compat.php';
	require dirname( __FILE__ ) . '/classes/class-profiles-registration-theme-compat.php';
}

/**
 * Handle the display of the profile page by loading the correct template file.
 *
 * @since 1.5.0
 */
function profiles_members_screen_display_profile() {

	/**
	 * Fires right before the loading of the Member profile screen template file.
	 *
	 * @since 1.5.0
	 */
	do_action( 'profiles_members_screen_display_profile' );

	/**
	 * Filters the template to load for the Member profile page screen.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template Path to the Member template to load.
	 */
	profiles_core_load_template( apply_filters( 'profiles_members_screen_display_profile', 'members/single/home' ) );
}

/**
 * Handle the display of the members directory index.
 *
 * @since 1.5.0
 */
function profiles_members_screen_index() {
	if ( profiles_is_members_directory() ) {
		profiles_update_is_directory( true, 'members' );

		/**
		 * Fires right before the loading of the Member directory index screen template file.
		 *
		 * @since 1.5.0
		 */
		do_action( 'profiles_members_screen_index' );

		/**
		 * Filters the template to load for the Member directory page screen.
		 *
		 * @since 1.5.0
		 *
		 * @param string $value Path to the member directory template to load.
		 */
		profiles_core_load_template( apply_filters( 'profiles_members_screen_index', 'members/index' ) );
	}
}
add_action( 'profiles_screens', 'profiles_members_screen_index' );

/**
 * Handle the loading of the Activate screen.
 *
 * @since 1.1.0
 *
 * @todo Move the actual activation process into an action in profiles-members-actions.php
 */
function profiles_core_screen_activation() {

	// Bail if not viewing the activation page.
	if ( ! profiles_is_current_component( 'activate' ) ) {
		return false;
	}

	// If the user is already logged in, redirect away from here.
	if ( is_user_logged_in() ) {

		// If activation page is also front page, set to members directory to
		// avoid an infinite loop. Otherwise, set to root domain.
		$redirect_to = profiles_is_component_front_page( 'activate' )
			? profiles_get_members_directory_permalink()
			: profiles_get_root_domain();

		// Trailing slash it, as we expect these URL's to be.
		$redirect_to = trailingslashit( $redirect_to );

		/**
		 * Filters the URL to redirect logged in users to when visiting activation page.
		 *
		 * @since 1.9.0
		 *
		 * @param string $redirect_to URL to redirect user to.
		 */
		$redirect_to = apply_filters( 'profiles_loggedin_activate_page_redirect_to', $redirect_to );

		// Redirect away from the activation page.
		profiles_core_redirect( $redirect_to );
	}

	// Grab the key (the old way).
	$key = isset( $_GET['key'] ) ? $_GET['key'] : '';

	// Grab the key (the new way).
	if ( empty( $key ) ) {
		$key = profiles_current_action();
	}

	// Get Profiles.
	$profiles = profiles();

	// We've got a key; let's attempt to activate the signup.
	if ( ! empty( $key ) ) {

		/**
		 * Filters the activation signup.
		 *
		 * @since 1.1.0
		 *
		 * @param bool|int $value Value returned by activation.
		 *                        Integer on success, boolean on failure.
		 */
		$user = apply_filters( 'profiles_core_activate_account', profiles_core_activate_signup( $key ) );

		// If there were errors, add a message and redirect.
		if ( ! empty( $user->errors ) ) {
			profiles_core_add_message( $user->get_error_message(), 'error' );
			profiles_core_redirect( trailingslashit( profiles_get_root_domain() . '/' . $profiles->pages->activate->slug ) );
		}

		profiles_core_add_message( __( 'Your account is now active!', 'profiles' ) );
		$profiles->activation_complete = true;
	}

	/**
	 * Filters the template to load for the Member activation page screen.
	 *
	 * @since 1.1.1
	 *
	 * @param string $value Path to the Member activation template to load.
	 */
	profiles_core_load_template( apply_filters( 'profiles_core_template_activate', array( 'activate', 'registration/activate' ) ) );
}
add_action( 'profiles_screens', 'profiles_core_screen_activation' );

/** Theme Compatibility *******************************************************/

new Profiles_Members_Theme_Compat();
new Profiles_Registration_Theme_Compat();
