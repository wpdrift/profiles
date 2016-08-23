<?php
/**
 * Profiles - Users Cover Image Header
 *
 * @package Profiles
 * @suprofilesackage profiles-legacy
 */

?>

<?php

/**
 * Fires before the display of a member's header.
 *
 * @since 1.2.0
 */
do_action( 'profiles_before_member_header' ); ?>

<div id="cover-image-container">
	<a id="header-cover-image" href="<?php profiles_displayed_user_link(); ?>"></a>

	<div id="item-header-cover-image">
		<div id="item-header-avatar">
			<a href="<?php profiles_displayed_user_link(); ?>">

				<?php profiles_displayed_user_avatar( 'type=full' ); ?>

			</a>
		</div><!-- #item-header-avatar -->

		<div id="item-header-content">

			<div id="item-buttons"><?php

				/**
				 * Fires in the member header actions section.
				 *
				 * @since 1.2.6
				 */
				do_action( 'profiles_member_header_actions' ); ?></div><!-- #item-buttons -->

			<span class="activity"><?php profiles_last_activity( profiles_displayed_user_id() ); ?></span>

			<?php

			/**
			 * Fires before the display of the member's header meta.
			 *
			 * @since 1.2.0
			 */
			do_action( 'profiles_before_member_header_meta' ); ?>

			<div id="item-meta">

				<?php

				 /**
				  * Fires after the group header actions section.
				  *
				  * If you'd like to show specific profile fields here use:
				  * profiles_member_profile_data( 'field=About Me' ); -- Pass the name of the field
				  *
				  * @since 1.2.0
				  */
				 do_action( 'profiles_profile_header_meta' );

				 ?>

			</div><!-- #item-meta -->

		</div><!-- #item-header-content -->

	</div><!-- #item-header-cover-image -->
</div><!-- #cover-image-container -->

<?php

/**
 * Fires after the display of a member's header.
 *
 * @since 1.2.0
 */
do_action( 'profiles_after_member_header' ); ?>

<?php

/** This action is documented in profiles-templates/profiles-legacy/profiles/activity/index.php */
do_action( 'template_notices' ); ?>
