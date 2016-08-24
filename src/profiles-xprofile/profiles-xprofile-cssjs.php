<?php
/**
 * Profiles XProfile CSS and JS.
 *
 * @package Profiles
 * @suprofilesackage XProfileScripts
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the CSS for XProfile admin styling.
 *
 * @since 1.1.0
 */
function profiles_xprofile_add_admin_css() {
	if ( !empty( $_GET['page'] ) && strpos( $_GET['page'], 'profiles-profile-setup' ) !== false ) {
		$min = profiles_core_get_minified_asset_suffix();

		wp_enqueue_style( 'xprofile-admin-css', profiles()->plugin_url . "profiles-xprofile/admin/css/admin{$min}.css", array(), profiles_get_version() );

		wp_style_add_data( 'xprofile-admin-css', 'rtl', true );
		if ( $min ) {
			wp_style_add_data( 'xprofile-admin-css', 'suffix', $min );
		}
	}
}
add_action( 'profiles_admin_enqueue_scripts', 'profiles_xprofile_add_admin_css' );

/**
 * Enqueue the jQuery libraries for handling drag/drop/sort.
 *
 * @since 1.5.0
 */
function profiles_xprofile_add_admin_js() {
	if ( !empty( $_GET['page'] ) && strpos( $_GET['page'], 'profiles-profile-setup' ) !== false ) {
		wp_enqueue_script( 'jquery-ui-core'      );
		wp_enqueue_script( 'jquery-ui-tabs'      );
		wp_enqueue_script( 'jquery-ui-mouse'     );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-sortable'  );

		$min = profiles_core_get_minified_asset_suffix();
		wp_enqueue_script( 'xprofile-admin-js', profiles()->plugin_url . "profiles-xprofile/admin/js/admin{$min}.js", array( 'jquery', 'jquery-ui-sortable' ), profiles_get_version() );

		// Localize strings.
		// supports_options_field_types is a dynamic list of field
		// types that support options, for use in showing/hiding the
		// "please enter options for this field" section.
		$strings = array(
			'supports_options_field_types' => array(),
			'do_autolink' => '',
		);

		foreach ( profiles_profiles_xprofile_get_field_types() as $field_type => $field_type_class ) {
			$field = new $field_type_class();
			if ( $field->supports_options ) {
				$strings['supports_options_field_types'][] = $field_type;
			}
		}

		// Load 'autolink' setting into JS so that we can provide smart defaults when switching field type.
		if ( ! empty( $_GET['field_id'] ) ) {
			$field_id = intval( $_GET['field_id'] );

			// Pull the raw data from the DB so we can tell whether the admin has saved a value yet.
			$strings['do_autolink'] = profiles_xprofile_get_meta( $field_id, 'field', 'do_autolink' );
		}

		wp_localize_script( 'xprofile-admin-js', 'XProfileAdmin', $strings );
	}
}
add_action( 'profiles_admin_enqueue_scripts', 'profiles_xprofile_add_admin_js', 1 );
