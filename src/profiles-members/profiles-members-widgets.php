<?php
/**
 * Profiles Members Widgets.
 *
 * @package Profiles
 * @suprofilesackage MembersWidgets
 * @since 2.2.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! profiles()->do_autoload ) {
	require dirname( __FILE__ ) . '/classes/class-profiles-core-members-widget.php';
	require dirname( __FILE__ ) . '/classes/class-profiles-core-whos-online-widget.php';
	require dirname( __FILE__ ) . '/classes/class-profiles-core-recently-active-widget.php';
}

/**
 * Register profiles-members widgets.
 *
 * Previously, these widgets were registered in profiles-core.
 *
 * @since 2.2.0
 */
function profiles_members_register_widgets() {
	add_action( 'widgets_init', create_function( '', 'return register_widget("Profiles_Core_Members_Widget");'         ) );
	add_action( 'widgets_init', create_function( '', 'return register_widget("Profiles_Core_Whos_Online_Widget");'     ) );
	add_action( 'widgets_init', create_function( '', 'return register_widget("Profiles_Core_Recently_Active_Widget");' ) );
}
add_action( 'profiles_register_widgets', 'profiles_members_register_widgets' );

/**
 * AJAX request handler for Members widgets.
 *
 * @since 1.0.0
 *
 * @see Profiles_Core_Members_Widget
 */
function profiles_core_ajax_widget_members() {

	check_ajax_referer( 'profiles_core_widget_members' );

	// Setup some variables to check.
	$filter      = ! empty( $_POST['filter']      ) ? $_POST['filter']                : 'recently-active-members';
	$max_members = ! empty( $_POST['max-members'] ) ? absint( $_POST['max-members'] ) : 5;

	// Determine the type of members query to perform.
	switch ( $filter ) {

		// Newest activated.
		case 'newest-members' :
			$type = 'newest';
			break;

		// Popular by friends.
		case 'popular-members' :
			if ( profiles_is_active( 'friends' ) ) {
				$type = 'popular';
			} else {
				$type = 'active';
			}
			break;

		// Default.
		case 'recently-active-members' :
		default :
			$type = 'active';
			break;
	}

	// Setup args for querying members.
	$members_args = array(
		'user_id'         => 0,
		'type'            => $type,
		'per_page'        => $max_members,
		'max'             => $max_members,
		'populate_extras' => true,
		'search_terms'    => false,
	);

	// Query for members.
	if ( profiles_has_members( $members_args ) ) : ?>
		<?php echo '0[[SPLIT]]'; // Return valid result. TODO: remove this. ?>
		<?php while ( profiles_members() ) : profiles_the_member(); ?>
			<li class="vcard">
				<div class="item-avatar">
					<a href="<?php profiles_member_permalink(); ?>"><?php profiles_member_avatar(); ?></a>
				</div>

				<div class="item">
					<div class="item-title fn"><a href="<?php profiles_member_permalink(); ?>" title="<?php profiles_member_name(); ?>"><?php profiles_member_name(); ?></a></div>
					<?php if ( 'active' === $type ) : ?>
						<div class="item-meta"><span class="activity"><?php profiles_member_last_active(); ?></span></div>
					<?php elseif ( 'newest' === $type ) : ?>
						<div class="item-meta"><span class="activity"><?php profiles_member_registered(); ?></span></div>
					<?php elseif ( profiles_is_active( 'friends' ) ) : ?>
						<div class="item-meta"><span class="activity"><?php profiles_member_total_friend_count(); ?></span></div>
					<?php endif; ?>
				</div>
			</li>

		<?php endwhile; ?>

	<?php else: ?>
		<?php echo "-1[[SPLIT]]<li>"; ?>
		<?php esc_html_e( 'There were no members found, please try another filter.', 'profiles' ) ?>
		<?php echo "</li>"; ?>
	<?php endif;
}
add_action( 'wp_ajax_widget_members',        'profiles_core_ajax_widget_members' );
add_action( 'wp_ajax_nopriv_widget_members', 'profiles_core_ajax_widget_members' );
