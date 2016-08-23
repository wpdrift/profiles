<?php
/**
 * Profiles - Members Profile Change Cover Image
 *
 * @package Profiles
 * @suprofilesackage profiles-legacy
 */

?>

<h2><?php _e( 'Change Cover Image', 'profiles' ); ?></h2>

<?php

/**
 * Fires before the display of profile cover image upload content.
 *
 * @since 2.4.0
 */
do_action( 'profiles_before_profile_edit_cover_image' ); ?>

<p><?php _e( 'Your Cover Image will be used to customize the header of your profile.', 'profiles' ); ?></p>

<?php profiles_attachments_get_template_part( 'cover-images/index' ); ?>

<?php

/**
 * Fires after the display of profile cover image upload content.
 *
 * @since 2.4.0
 */
do_action( 'profiles_after_profile_edit_cover_image' ); ?>
