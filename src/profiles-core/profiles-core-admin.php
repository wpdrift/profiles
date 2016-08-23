<?php
/**
 * Main Profiles Admin Class.
 *
 * @package Profiles
 * @suprofilesackage CoreAdministration
 * @since 0.0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-admin.php';
}

/**
 * Setup Profiles Admin.
 *
 * @since 0.0.1
 *
 */
function profiles_admin() {
	profiles()->admin = new Profiles_Admin();
	return;


	// These are strings we may use to describe maintenance/security releases, where we aim for no new strings.
	_n_noop( 'Maintenance Release', 'Maintenance Releases', 'profiles' );
	_n_noop( 'Security Release', 'Security Releases', 'profiles' );
	_n_noop( 'Maintenance and Security Release', 'Maintenance and Security Releases', 'profiles' );

	/* translators: 1: WordPress version number. */
	_n_noop( '<strong>Version %1$s</strong> addressed a security issue.',
	         '<strong>Version %1$s</strong> addressed some security issues.',
	         'profiles' );

	/* translators: 1: WordPress version number, 2: plural number of bugs. */
	_n_noop( '<strong>Version %1$s</strong> addressed %2$s bug.',
	         '<strong>Version %1$s</strong> addressed %2$s bugs.',
	         'profiles' );

	/* translators: 1: WordPress version number, 2: plural number of bugs. Singular security issue. */
	_n_noop( '<strong>Version %1$s</strong> addressed a security issue and fixed %2$s bug.',
	         '<strong>Version %1$s</strong> addressed a security issue and fixed %2$s bugs.',
	         'profiles' );

	/* translators: 1: WordPress version number, 2: plural number of bugs. More than one security issue. */
	_n_noop( '<strong>Version %1$s</strong> addressed some security issues and fixed %2$s bug.',
	         '<strong>Version %1$s</strong> addressed some security issues and fixed %2$s bugs.',
	         'profiles' );

	__( 'For more information, see <a href="%s">the release notes</a>.', 'profiles' );
}
