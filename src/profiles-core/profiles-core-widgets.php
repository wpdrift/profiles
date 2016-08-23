<?php
/**
 * Profiles Core Component Widgets.
 *
 * @package Profiles
 * @suprofilesackage Core
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-core-login-widget.php';
}

/**
 * Register profiles-core widgets.
 *
 * @since 1.0.0
 */
function profiles_core_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("Profiles_Core_Login_Widget");') );
}
add_action( 'profiles_register_widgets', 'profiles_core_register_widgets' );
