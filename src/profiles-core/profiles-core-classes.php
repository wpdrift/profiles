<?php
/**
 * Core component classes.
 *
 * @package Profiles
 * @suprofilesackage Core
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

require dirname( __FILE__ ) . '/classes/class-profiles-user-query.php';
require dirname( __FILE__ ) . '/classes/class-profiles-core-user.php';
require dirname( __FILE__ ) . '/classes/class-profiles-date-query.php';
require dirname( __FILE__ ) . '/classes/class-profiles-core-notification.php';
require dirname( __FILE__ ) . '/classes/class-profiles-button.php';
require dirname( __FILE__ ) . '/classes/class-profiles-embed.php';
require dirname( __FILE__ ) . '/classes/class-profiles-walker-nav-menu.php';
require dirname( __FILE__ ) . '/classes/class-profiles-walker-nav-menu-checklist.php';
require dirname( __FILE__ ) . '/classes/class-profiles-suggestions.php';
require dirname( __FILE__ ) . '/classes/class-profiles-members-suggestions.php';
require dirname( __FILE__ ) . '/classes/class-profiles-recursive-query.php';
require dirname( __FILE__ ) . '/classes/class-profiles-core-sort-by-key-callback.php';
require dirname( __FILE__ ) . '/classes/class-profiles-media-extractor.php';
require dirname( __FILE__ ) . '/classes/class-profiles-attachment.php';
require dirname( __FILE__ ) . '/classes/class-profiles-attachment-avatar.php';
require dirname( __FILE__ ) . '/classes/class-profiles-attachment-cover-image.php';
require dirname( __FILE__ ) . '/classes/class-profiles-email-recipient.php';
require dirname( __FILE__ ) . '/classes/class-profiles-email.php';
require dirname( __FILE__ ) . '/classes/class-profiles-email-delivery.php';
require dirname( __FILE__ ) . '/classes/class-profiles-phpmailer.php';
require dirname( __FILE__ ) . '/classes/class-profiles-core-nav.php';
require dirname( __FILE__ ) . '/classes/class-profiles-core-nav-item.php';
require dirname( __FILE__ ) . '/classes/class-profiles-core-oembed-extension.php';

if ( profiles()->do_nav_backcompat ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-core-profiles-nav-backcompat.php';
	require dirname( __FILE__ ) . '/classes/class-profiles-core-profiles-options-nav-backcompat.php';
}
