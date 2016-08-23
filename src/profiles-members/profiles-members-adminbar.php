<?php
/**
 * Profiles Members Toolbar.
 *
 * Handles the member functions related to the WordPress Toolbar.
 *
 * @package Profiles
 * @suprofilesackage MembersAdminBar
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add the "My Account" menu and all submenus.
 *
 * @since 1.6.0
 *
 * @todo Deprecate WP 3.2 Toolbar compatibility when we drop 3.2 support.
 */
function profiles_members_admin_bar_my_account_menu() {
	global $wp_admin_bar;

	// Bail if this is an ajax request.
	if ( defined( 'DOING_AJAX' ) )
		return;

	// Logged in user.
	if ( is_user_logged_in() ) {

		$profiles = profiles();

		// Stored in the global so we can add menus easily later on.
		$profiles->my_account_menu_id = 'my-account-profiles';

		// Create the main 'My Account' menu.
		$wp_admin_bar->add_menu( array(
			'id'     => $profiles->my_account_menu_id,
			'group'  => true,
			'title'  => __( 'Edit My Profile', 'profiles' ),
			'href'   => profiles_loggedin_user_domain(),
			'meta'   => array(
			'class'  => 'ab-sub-secondary'
		) ) );

		// Show login and sign-up links.
	} elseif ( !empty( $wp_admin_bar ) ) {

		add_filter( 'show_admin_bar', '__return_true' );

		// Create the main 'My Account' menu.
		$wp_admin_bar->add_menu( array(
			'id'    => 'profiles-login',
			'title' => __( 'Log in', 'profiles' ),
			'href'  => wp_login_url( profiles_get_requested_url() )
		) );

	}
}
//add_action( 'profiles_setup_admin_bar', 'profiles_members_admin_bar_my_account_menu', 4 );

/**
 * Add the User Admin top-level menu to user pages.
 *
 * @since 1.5.0
 */
function profiles_members_admin_bar_user_admin_menu() {
	global $wp_admin_bar;

	// Only show if viewing a user.
	if ( !profiles_is_user() )
		return false;

	// Don't show this menu to non site admins or if you're viewing your own profile.
	if ( !current_user_can( 'edit_users' ) || profiles_is_my_profile() )
		return false;

	$profiles = profiles();

	// Unique ID for the 'My Account' menu.
	$profiles->user_admin_menu_id = 'user-admin';

	// Add the top-level User Admin button.
	$wp_admin_bar->add_menu( array(
		'id'    => $profiles->user_admin_menu_id,
		'title' => __( 'Edit Member', 'profiles' ),
		'href'  => profiles_displayed_user_domain()
	) );

	if ( profiles_is_active( 'xprofile' ) ) {
		// User Admin > Edit this user's profile.
		$wp_admin_bar->add_menu( array(
			'parent' => $profiles->user_admin_menu_id,
			'id'     => $profiles->user_admin_menu_id . '-edit-profile',
			'title'  => __( "Edit Profile", 'profiles' ),
			'href'   => profiles_get_members_component_link( 'profile', 'edit' )
		) );

		// User Admin > Edit this user's avatar.
		if ( profiles()->avatar->show_avatars ) {
			$wp_admin_bar->add_menu( array(
				'parent' => $profiles->user_admin_menu_id,
				'id'     => $profiles->user_admin_menu_id . '-change-avatar',
				'title'  => __( "Edit Profile Photo", 'profiles' ),
				'href'   => profiles_get_members_component_link( 'profile', 'change-avatar' )
			) );
		}

		// User Admin > Edit this user's cover image.
		if ( profiles_displayed_user_use_cover_image_header() ) {
			$wp_admin_bar->add_menu( array(
				'parent' => $profiles->user_admin_menu_id,
				'id'     => $profiles->user_admin_menu_id . '-change-cover-image',
				'title'  => __( 'Edit Cover Image', 'profiles' ),
				'href'   => profiles_get_members_component_link( 'profile', 'change-cover-image' )
			) );
		}

	}

}
add_action( 'admin_bar_menu', 'profiles_members_admin_bar_user_admin_menu', 99 );

/**
 * Remove rogue WP core Edit menu when viewing a single user.
 *
 * @since 1.6.0
 */
function profiles_members_remove_edit_page_menu() {
	if ( profiles_is_user() ) {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_edit_menu', 80 );
	}
}
add_action( 'add_admin_bar_menus', 'profiles_members_remove_edit_page_menu' );
