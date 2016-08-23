<?php
/**
 * Profiles - Members Profile Change Avatar
 *
 * @package Profiles
 * @suprofilesackage profiles-legacy
 */

?>

<h2><?php _e( 'Change Profile Photo', 'profiles' ); ?></h2>

<?php

/**
 * Fires before the display of profile avatar upload content.
 *
 * @since 1.1.0
 */
do_action( 'profiles_before_profile_avatar_upload_content' ); ?>

<?php if ( !(int)profiles_get_option( 'profiles-disable-avatar-uploads' ) ) : ?>

	<p><?php _e( 'Your profile photo will be used on your profile and throughout the site. If there is a <a href="http://gravatar.com">Gravatar</a> associated with your account email we will use that, or you can upload an image from your computer.', 'profiles' ); ?></p>

	<form action="" method="post" id="avatar-upload-form" class="standard-form" enctype="multipart/form-data">

		<?php if ( 'upload-image' == profiles_get_avatar_admin_step() ) : ?>

			<?php wp_nonce_field( 'profiles_avatar_upload' ); ?>
			<p><?php _e( 'Click below to select a JPG, GIF or PNG format photo from your computer and then click \'Upload Image\' to proceed.', 'profiles' ); ?></p>

			<p id="avatar-upload">
				<label for="file" class="profiles-screen-reader-text"><?php
					/* translators: accessibility text */
					_e( 'Select an image', 'profiles' );
				?></label>
				<input type="file" name="file" id="file" />
				<input type="submit" name="upload" id="upload" value="<?php esc_attr_e( 'Upload Image', 'profiles' ); ?>" />
				<input type="hidden" name="action" id="action" value="profiles_avatar_upload" />
			</p>

			<?php if ( profiles_get_user_has_avatar() ) : ?>
				<p><?php _e( "If you'd like to delete your current profile photo but not upload a new one, please use the delete profile photo button.", 'profiles' ); ?></p>
				<p><a class="button edit" href="<?php profiles_avatar_delete_link(); ?>"><?php _e( 'Delete My Profile Photo', 'profiles' ); ?></a></p>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( 'crop-image' == profiles_get_avatar_admin_step() ) : ?>

			<h5><?php _e( 'Crop Your New Profile Photo', 'profiles' ); ?></h5>

			<img src="<?php profiles_avatar_to_crop(); ?>" id="avatar-to-crop" class="avatar" alt="<?php esc_attr_e( 'Profile photo to crop', 'profiles' ); ?>" />

			<div id="avatar-crop-pane">
				<img src="<?php profiles_avatar_to_crop(); ?>" id="avatar-crop-preview" class="avatar" alt="<?php esc_attr_e( 'Profile photo preview', 'profiles' ); ?>" />
			</div>

			<input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php esc_attr_e( 'Crop Image', 'profiles' ); ?>" />

			<input type="hidden" name="image_src" id="image_src" value="<?php profiles_avatar_to_crop_src(); ?>" />
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />

			<?php wp_nonce_field( 'profiles_avatar_cropstore' ); ?>

		<?php endif; ?>

	</form>

	<?php
	/**
	 * Load the Avatar UI templates
	 *
	 * @since  2.3.0
	 */
	profiles_avatar_get_templates(); ?>

<?php else : ?>

	<p><?php _e( 'Your profile photo will be used on your profile and throughout the site. To change your profile photo, please create an account with <a href="http://gravatar.com">Gravatar</a> using the same email address as you used to register with this site.', 'profiles' ); ?></p>

<?php endif; ?>

<?php

/**
 * Fires after the display of profile avatar upload content.
 *
 * @since 1.1.0
 */
do_action( 'profiles_after_profile_avatar_upload_content' ); ?>
