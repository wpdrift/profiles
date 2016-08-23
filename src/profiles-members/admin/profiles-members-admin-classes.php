<?php
/**
 * Profiles Members List Classes.
 *
 * @package Profiles
 * @suprofilesackage MembersAdminClasses
 * @since 2.3.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_Users_List_Table' ) ) {
	require dirname( dirname( __FILE__ ) ) . '/classes/class-profiles-members-list-table.php';
}

if ( class_exists( 'WP_MS_Users_List_Table' ) ) {
	require dirname( dirname( __FILE__ ) ) . '/classes/class-profiles-members-ms-list-table.php';
}
