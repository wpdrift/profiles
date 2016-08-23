<?php
/**
 * Profiles Admin Actions.
 *
 * This file contains the actions that are used through-out Profiles Admin. They
 * are consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional actions can currently be found.
 *
 *  - Profiles: In {@link Profiles::setup_actions()} in Profiles.php
 *  - Admin: More in {@link profiles_Admin::setup_actions()} in admin.php
 *
 * @package Profiles
 * @suprofilesackage Admin
 * @since 2.3.0
 * @see profiles-core-actions.php
 * @see profiles-core-filters.php
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attach Profiles to WordPress.
 *
 * Profiles uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when Profiles is
 * installed and activated, without needing to do complicated guesswork.
 *
 * For more information on how this works, see the 'Plugin Dependency' section
 * near the bottom of this file.
 *
 *          v--WordPress Actions       v--Profiles Sub-actions
 */
add_action( 'admin_menu',                         'profiles_admin_menu'                    );
add_action( 'admin_init',                         'profiles_admin_init'                    );
add_action( 'admin_head',                         'profiles_admin_head'                    );
add_action( 'admin_notices',                      'profiles_admin_notices'                 );
add_action( 'admin_enqueue_scripts',              'profiles_admin_enqueue_scripts'         );
add_action( 'customize_controls_enqueue_scripts', 'profiles_admin_enqueue_scripts', 8      );
add_action( 'network_admin_menu',                 'profiles_admin_menu'                    );
add_action( 'custom_menu_order',                  'profiles_admin_custom_menu_order'       );
add_action( 'menu_order',                         'profiles_admin_menu_order'              );
add_action( 'wpmu_new_blog',                      'profiles_new_site',               10, 6 );

// Hook on to admin_init.
add_action( 'profiles_admin_init', 'profiles_setup_updater',          1000 );
add_action( 'profiles_admin_init', 'profiles_core_activation_notice', 1010 );
add_action( 'profiles_admin_init', 'profiles_register_importers'           );
add_action( 'profiles_admin_init', 'profiles_register_admin_style'         );
add_action( 'profiles_admin_init', 'profiles_register_admin_settings'      );
add_action( 'profiles_admin_init', 'profiles_do_activation_redirect', 1    );

// Add a new separator.
add_action( 'profiles_admin_menu', 'profiles_admin_separator' );

/**
 * When a new site is created in a multisite installation, run the activation
 * routine on that site.
 *
 * @since 1.7.0
 *
 * @param int    $blog_id ID of the blog being installed to.
 * @param int    $user_id ID of the user the install is for.
 * @param string $domain  Domain to use with the install.
 * @param string $path    Path to use with the install.
 * @param int    $site_id ID of the site being installed to.
 * @param array  $meta    Metadata to use with the site creation.
 */
function profiles_new_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	// Bail if plugin is not network activated.
	if ( ! is_plugin_active_for_network( profiles()->basename ) )
		return;

	// Switch to the new blog.
	switch_to_blog( $blog_id );

	/**
	 * Fires the activation routine for a new site created in a multisite installation.
	 *
	 * @since 1.7.0
	 *
	 * @param int    $blog_id ID of the blog being installed to.
	 * @param int    $user_id ID of the user the install is for.
	 * @param string $domain  Domain to use with the install.
	 * @param string $path    Path to use with the install.
	 * @param int    $site_id ID of the site being installed to.
	 * @param array  $meta    Metadata to use with the site creation.
	 */
	do_action( 'profiles_new_site', $blog_id, $user_id, $domain, $path, $site_id, $meta );

	// Restore original blog.
	restore_current_blog();
}

/** Sub-Actions ***************************************************************/

/**
 * Piggy back admin_init action.
 *
 * @since 1.7.0
 *
 */
function profiles_admin_init() {

	/**
	 * Fires inside the profiles_admin_init function.
	 *
	 * @since 1.6.0
	 */
	do_action( 'profiles_admin_init' );
}

/**
 * Piggy back admin_menu action.
 *
 * @since 1.7.0
 *
 */
function profiles_admin_menu() {

	/**
	 * Fires inside the profiles_admin_menu function.
	 *
	 * @since 1.7.0
	 */
	do_action( 'profiles_admin_menu' );
}

/**
 * Piggy back admin_head action.
 *
 * @since 1.7.0
 *
 */
function profiles_admin_head() {

	/**
	 * Fires inside the profiles_admin_head function.
	 *
	 * @since 1.6.0
	 */
	do_action( 'profiles_admin_head' );
}

/**
 * Piggy back admin_notices action.
 *
 * @since 1.7.0
 *
 */
function profiles_admin_notices() {

	/**
	 * Fires inside the profiles_admin_notices function.
	 *
	 * @since 1.5.0
	 */
	do_action( 'profiles_admin_notices' );
}

/**
 * Piggy back admin_enqueue_scripts action.
 *
 * @since 1.7.0
 *
 * @param string $hook_suffix The current admin page, passed to
 *                            'admin_enqueue_scripts'.
 */
function profiles_admin_enqueue_scripts( $hook_suffix = '' ) {

	/**
	 * Fires inside the profiles_admin_enqueue_scripts function.
	 *
	 * @since 1.7.0
	 *
	 * @param string $hook_suffix The current admin page, passed to admin_enqueue_scripts.
	 */
	do_action( 'profiles_admin_enqueue_scripts', $hook_suffix );
}

/**
 * Dedicated action to register Profiles importers.
 *
 * @since 1.7.0
 *
 */
function profiles_register_importers() {

	/**
	 * Fires inside the profiles_register_importers function.
	 *
	 * Used to register a Profiles importer.
	 *
	 * @since 1.7.0
	 */
	do_action( 'profiles_register_importers' );
}

/**
 * Dedicated action to register admin styles.
 *
 * @since 1.7.0
 *
 */
function profiles_register_admin_style() {

	/**
	 * Fires inside the profiles_register_admin_style function.
	 *
	 * @since 1.7.0
	 */
	do_action( 'profiles_register_admin_style' );
}

/**
 * Dedicated action to register admin settings.
 *
 * @since 1.7.0
 *
 */
function profiles_register_admin_settings() {

	/**
	 * Fires inside the profiles_register_admin_settings function.
	 *
	 * @since 1.6.0
	 */
	do_action( 'profiles_register_admin_settings' );
}
