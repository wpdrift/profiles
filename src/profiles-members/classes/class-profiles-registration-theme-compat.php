<?php
/**
 * Profiles Member Screens.
 *
 * Handlers for member screens that aren't handled elsewhere.
 *
 * @package Profiles
 * @suprofilesackage MembersScreens
 * @since 1.7.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The main theme compat class for Profiles Registration.
 *
 * This class sets up the necessary theme compatibility actions to safely output
 * registration template parts to the_title and the_content areas of a theme.
 *
 * @since 1.7.0
 */
class Profiles_Registration_Theme_Compat {

	/**
	 * Setup the groups component theme compatibility.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		add_action( 'profiles_setup_theme_compat', array( $this, 'is_registration' ) );
	}

	/**
	 * Are we looking at either the registration or activation pages?
	 *
	 * @since 1.7.0
	 */
	public function is_registration() {

		// Bail if not looking at the registration or activation page.
		if ( ! profiles_is_register_page() && ! profiles_is_activation_page() ) {
			return;
		}

		// Not a directory.
		profiles_update_is_directory( false, 'register' );

		// Setup actions.
		add_filter( 'profiles_get_profiles_template',                array( $this, 'template_hierarchy' ) );
		add_filter( 'profiles_replace_the_content',                    array( $this, 'dummy_content' ) );
	}

	/** Template ***********************************************************/

	/**
	 * Add template hierarchy to theme compat for registration/activation pages.
	 *
	 * This is to mirror how WordPress has
	 * {@link https://codex.wordpress.org/Template_Hierarchy template hierarchy}.
	 *
	 * @since 1.8.0
	 *
	 * @param string $templates The templates from profiles_get_theme_compat_templates().
	 * @return array $templates Array of custom templates to look for.
	 */
	public function template_hierarchy( $templates ) {
		$component = sanitize_file_name( profiles_current_component() );

		/**
		 * Filters the template hierarchy for theme compat and registration/activation pages.
		 *
		 * This filter is a variable filter that depends on the current component
		 * being used.
		 *
		 * @since 1.8.0
		 *
		 * @param array $value Array of template paths to add to hierarchy.
		 */
		$new_templates = apply_filters( "profiles_template_hierarchy_{$component}", array(
			"members/index-{$component}.php"
		) );

		// Merge new templates with existing stack
		// @see profiles_get_theme_compat_templates().
		$templates = array_merge( (array) $new_templates, $templates );

		return $templates;
	}

	/**
	 * Filter the_content with either the register or activate templates.
	 *
	 * @since 1.7.0
	 */
	public function dummy_content() {
		if ( profiles_is_register_page() ) {
			return profiles_buffer_template_part( 'members/register', null, false );
		} else {
			return profiles_buffer_template_part( 'members/activate', null, false );
		}
	}
}
