<?php
/**
 * Profiles Member Theme Compat.
 *
 * @package Profiles
 * @suprofilesackage MembersScreens
 * @since 1.7.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The main theme compat class for Profiles Members.
 *
 * This class sets up the necessary theme compatibility actions to safely output
 * member template parts to the_title and the_content areas of a theme.
 *
 * @since 1.7.0
 */
class Profiles_Members_Theme_Compat {

	/**
	 * Set up the members component theme compatibility.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		add_action( 'profiles_setup_theme_compat', array( $this, 'is_members' ) );
	}

	/**
	 * Are we looking at something that needs members theme compatibility?
	 *
	 * @since 1.7.0
	 */
	public function is_members() {

		// Bail if not looking at the members component or a user's page.
		if ( ! profiles_is_members_component() && ! profiles_is_user() ) {
			return;
		}

		// Members Directory.
		if ( ! profiles_current_action() && ! profiles_current_item() ) {
			profiles_update_is_directory( true, 'members' );

			/**
			 * Fires if looking at Members directory when needing theme compat.
			 *
			 * @since 1.5.0
			 */
			do_action( 'profiles_members_screen_index' );

			add_filter( 'profiles_get_profiles_template',                array( $this, 'directory_template_hierarchy' ) );
			add_action( 'profiles_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			add_filter( 'profiles_replace_the_content',                    array( $this, 'directory_content'    ) );

		// User page.
		} elseif ( profiles_is_user() ) {

			/**
			 * Fires if looking at Members user page when needing theme compat.
			 *
			 * @since 1.5.0
			 */
			do_action( 'profiles_members_screen_display_profile' );

			add_filter( 'profiles_get_profiles_template',                array( $this, 'single_template_hierarchy' ) );
			add_action( 'profiles_template_include_reset_dummy_post_data', array( $this, 'single_dummy_post'    ) );
			add_filter( 'profiles_replace_the_content',                    array( $this, 'single_dummy_content' ) );

		}
	}

	/** Directory *************************************************************/

	/**
	 * Add template hierarchy to theme compat for the members directory page.
	 *
	 * This is to mirror how WordPress has
	 * {@link https://codex.wordpress.org/Template_Hierarchy template hierarchy}.
	 *
	 * @since 1.8.0
	 *
	 * @param array $templates The templates from profiles_get_theme_compat_templates().
	 * @return array $templates Array of custom templates to look for.
	 */
	public function directory_template_hierarchy( $templates = array() ) {

		// Set up the template hierarchy.
		$new_templates = array();
		if ( '' !== profiles_get_current_member_type() ) {
			$new_templates[] = 'members/index-directory-type-' . sanitize_file_name( profiles_get_current_member_type() ) . '.php';
		}
		$new_templates[] = 'members/index-directory.php';

		/**
		 * Filters the template hierarchy for theme compat and members directory page.
		 *
		 * @since 1.8.0
		 *
		 * @param array $value Array of template paths to add to hierarchy.
		 */
		$new_templates = apply_filters( 'profiles_template_hierarchy_members_directory', $new_templates );

		// Merge new templates with existing stack
		// @see profiles_get_theme_compat_templates().
		$templates = array_merge( (array) $new_templates, $templates );

		return $templates;
	}

	/**
	 * Update the global $post with directory data.
	 *
	 * @since 1.7.0
	 */
	public function directory_dummy_post() {
		profiles_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => profiles_get_directory_title( 'members' ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with the members index template part.
	 *
	 * @since 1.7.0
	 */
	public function directory_content() {
		return profiles_buffer_template_part( 'members/index', null, false );
	}

	/** Single ****************************************************************/

	/**
	 * Add custom template hierarchy to theme compat for member pages.
	 *
	 * This is to mirror how WordPress has
	 * {@link https://codex.wordpress.org/Template_Hierarchy template hierarchy}.
	 *
	 * @since 1.8.0
	 *
	 * @param string $templates The templates from
	 *                          profiles_get_theme_compat_templates().
	 * @return array $templates Array of custom templates to look for.
	 */
	public function single_template_hierarchy( $templates ) {
		// Setup some variables we're going to reference in our custom templates.
		$user_nicename = profiles()->displayed_user->userdata->user_nicename;

		/**
		 * Filters the template hierarchy for theme compat and member pages.
		 *
		 * @since 1.8.0
		 *
		 * @param array $value Array of template paths to add to hierarchy.
		 */
		$new_templates = apply_filters( 'profiles_template_hierarchy_members_single_item', array(
			'members/single/index-id-'        . sanitize_file_name( profiles_displayed_user_id() ) . '.php',
			'members/single/index-nicename-'  . sanitize_file_name( $user_nicename )         . '.php',
			'members/single/index-action-'    . sanitize_file_name( profiles_current_action() )    . '.php',
			'members/single/index-component-' . sanitize_file_name( profiles_current_component() ) . '.php',
			'members/single/index.php'
		) );

		// Merge new templates with existing stack
		// @see profiles_get_theme_compat_templates().
		$templates = array_merge( (array) $new_templates, $templates );

		return $templates;
	}

	/**
	 * Update the global $post with the displayed user's data.
	 *
	 * @since 1.7.0
	 */
	public function single_dummy_post() {
		profiles_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => profiles_get_displayed_user_fullname(),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with the members' single home template part.
	 *
	 * @since 1.7.0
	 */
	public function single_dummy_content() {
		return profiles_buffer_template_part( 'members/single/home', null, false );
	}
}
