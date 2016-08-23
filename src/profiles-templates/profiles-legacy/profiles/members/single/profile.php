<?php
/**
 * Profiles - Users Profile
 *
 * @package Profiles
 * @suprofilesackage profiles-legacy
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php profiles_get_options_nav(); ?>
	</ul>
</div><!-- .item-list-tabs -->

<?php

/**
 * Fires before the display of member profile content.
 *
 * @since 1.1.0
 */
do_action( 'profiles_before_profile_content' ); ?>

<div class="profile">

<?php switch ( profiles_current_action() ) :

	// Edit
	case 'edit'   :
		profiles_get_template_part( 'members/single/profile/edit' );
		break;

	// Change Avatar
	case 'change-avatar' :
		profiles_get_template_part( 'members/single/profile/change-avatar' );
		break;

	// Change Cover Image
	case 'change-cover-image' :
		profiles_get_template_part( 'members/single/profile/change-cover-image' );
		break;

	// Compose
	case 'public' :

		// Display XProfile
		if ( profiles_is_active( 'xprofile' ) )
			profiles_get_template_part( 'members/single/profile/profile-loop' );

		// Display WordPress profile (fallback)
		else
			profiles_get_template_part( 'members/single/profile/profile-wp' );

		break;

	// Any other
	default :
		profiles_get_template_part( 'members/single/plugins' );
		break;
endswitch; ?>
</div><!-- .profile -->

<?php

/**
 * Fires after the display of member profile content.
 *
 * @since 1.1.0
 */
do_action( 'profiles_after_profile_content' ); ?>
