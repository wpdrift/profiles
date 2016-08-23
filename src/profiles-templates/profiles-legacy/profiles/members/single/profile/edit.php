<?php
/**
 * Profiles - Members Single Profile Edit
 *
 * @package Profiles
 * @suprofilesackage profiles-legacy
 */

/**
 * Fires after the display of member profile edit content.
 *
 * @since 1.1.0
 */
do_action( 'profiles_before_profile_edit_content' );

if ( profiles_has_profile( 'profile_group_id=' . profiles_get_current_profile_group_id() ) ) :
	while ( profiles_profile_groups() ) : profiles_the_profile_group(); ?>

<form action="<?php profiles_the_profile_group_edit_form_action(); ?>" method="post" id="profile-edit-form" class="standard-form <?php profiles_the_profile_group_slug(); ?>">

	<?php

		/** This action is documented in profiles-templates/profiles-legacy/profiles/members/single/profile/profile-wp.php */
		do_action( 'profiles_before_profile_field_content' ); ?>

		<h2><?php printf( __( "Editing '%s' Profile Group", 'profiles' ), profiles_get_the_profile_group_name() ); ?></h2>

		<?php if ( profiles_profile_has_multiple_groups() ) : ?>
			<ul class="button-nav">

				<?php profiles_profile_group_tabs(); ?>

			</ul>
		<?php endif ;?>

		<div class="clear"></div>

		<?php while ( profiles_profile_fields() ) : profiles_the_profile_field(); ?>

			<div<?php profiles_field_css_class( 'editfield' ); ?>>

				<?php
				$field_type = profiles_xprofile_create_field_type( profiles_get_the_profile_field_type() );
				$field_type->edit_field_html();

				/**
				 * Fires before the display of visibility options for the field.
				 *
				 * @since 1.7.0
				 */
				do_action( 'profiles_custom_profile_edit_fields_pre_visibility' );
				?>

				<?php if ( profiles_current_user_can( 'profiles_xprofile_change_field_visibility' ) ) : ?>
					<p class="field-visibility-settings-toggle" id="field-visibility-settings-toggle-<?php profiles_the_profile_field_id() ?>">
						<?php
						printf(
							__( 'This field can be seen by: %s', 'profiles' ),
							'<span class="current-visibility-level">' . profiles_get_the_profile_field_visibility_level_label() . '</span>'
						);
						?>
						<a href="#" class="visibility-toggle-link"><?php _e( 'Change', 'profiles' ); ?></a>
					</p>

					<div class="field-visibility-settings" id="field-visibility-settings-<?php profiles_the_profile_field_id() ?>">
						<fieldset>
							<legend><?php _e( 'Who can see this field?', 'profiles' ) ?></legend>

							<?php profiles_profile_visibility_radio_buttons() ?>

						</fieldset>
						<a class="field-visibility-settings-close" href="#"><?php _e( 'Close', 'profiles' ) ?></a>
					</div>
				<?php else : ?>
					<div class="field-visibility-settings-notoggle" id="field-visibility-settings-toggle-<?php profiles_the_profile_field_id() ?>">
						<?php
						printf(
							__( 'This field can be seen by: %s', 'profiles' ),
							'<span class="current-visibility-level">' . profiles_get_the_profile_field_visibility_level_label() . '</span>'
						);
						?>
					</div>
				<?php endif ?>

				<?php

				/**
				 * Fires after the visibility options for a field.
				 *
				 * @since 1.1.0
				 */
				do_action( 'profiles_custom_profile_edit_fields' ); ?>

				<p class="description"><?php profiles_the_profile_field_description(); ?></p>
			</div>

		<?php endwhile; ?>

	<?php

	/** This action is documented in profiles-templates/profiles-legacy/profiles/members/single/profile/profile-wp.php */
	do_action( 'profiles_after_profile_field_content' ); ?>

	<div class="submit">
		<input type="submit" name="profile-group-edit-submit" id="profile-group-edit-submit" value="<?php esc_attr_e( 'Save Changes', 'profiles' ); ?> " />
	</div>

	<input type="hidden" name="field_ids" id="field_ids" value="<?php profiles_the_profile_field_ids(); ?>" />

	<?php wp_nonce_field( 'profiles_xprofile_edit' ); ?>

</form>

<?php endwhile; endif; ?>

<?php

/**
 * Fires after the display of member profile edit content.
 *
 * @since 1.1.0
 */
do_action( 'profiles_after_profile_edit_content' ); ?>
