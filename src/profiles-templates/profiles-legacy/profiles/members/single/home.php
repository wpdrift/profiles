<?php
/**
 * Profiles - Members Home
 *
 * @package Profiles
 * @suprofilesackage profiles-legacy
 */

?>

<div id="profiles">

	<?php

	/**
	 * Fires before the display of member home content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'profiles_before_member_home_content' ); ?>

	<div id="item-header" role="complementary">

		<?php
		/**
		 * If the cover image feature is enabled, use a specific header
		 */
		if ( profiles_displayed_user_use_cover_image_header() ) :
			profiles_get_template_part( 'members/single/cover-image-header' );
		else :
			profiles_get_template_part( 'members/single/member-header' );
		endif;
		?>

	</div><!-- #item-header -->

	<div id="item-nav">
		<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
			<ul>

				<?php profiles_get_displayed_user_nav(); ?>

				<?php

				/**
				 * Fires after the display of member options navigation.
				 *
				 * @since 1.2.4
				 */
				do_action( 'profiles_member_options_nav' ); ?>

			</ul>
		</div>
	</div><!-- #item-nav -->

	<div id="item-body">

		<?php

		/**
		 * Fires before the display of member body content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'profiles_before_member_body' );

		if ( profiles_is_user_front() ) :
			profiles_displayed_user_front_template_part();

		elseif ( profiles_is_user_profile() ) :
			profiles_get_template_part( 'members/single/profile'  );

		// If nothing sticks, load a generic template
		else :
			profiles_get_template_part( 'members/single/plugins'  );

		endif;

		/**
		 * Fires after the display of member body content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'profiles_after_member_body' ); ?>

	</div><!-- #item-body -->

	<?php

	/**
	 * Fires after the display of member home content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'profiles_after_member_home_content' ); ?>

</div><!-- #profiles -->
