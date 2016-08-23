<?php
/**
 * Profiles - Members Profile Loop
 *
 * @package Profiles
 * @suprofilesackage profiles-legacy
 */

/** This action is documented in profiles-templates/profiles-legacy/profiles/members/single/profile/profile-wp.php */
do_action( 'profiles_before_profile_loop_content' ); ?>

<?php if ( profiles_has_profile() ) : ?>

	<?php while ( profiles_profile_groups() ) : profiles_the_profile_group(); ?>

		<?php if ( profiles_profile_group_has_fields() ) : ?>

			<?php

			/** This action is documented in profiles-templates/profiles-legacy/profiles/members/single/profile/profile-wp.php */
			do_action( 'profiles_before_profile_field_content' ); ?>

			<div class="profiles-widget <?php profiles_the_profile_group_slug(); ?>">

				<h2><?php profiles_the_profile_group_name(); ?></h2>

				<table class="profile-fields">

					<?php while ( profiles_profile_fields() ) : profiles_the_profile_field(); ?>

						<?php if ( profiles_field_has_data() ) : ?>

							<tr<?php profiles_field_css_class(); ?>>

								<td class="label"><?php profiles_the_profile_field_name(); ?></td>

								<td class="data"><?php profiles_the_profile_field_value(); ?></td>

							</tr>

						<?php endif; ?>

						<?php

						/**
						 * Fires after the display of a field table row for profile data.
						 *
						 * @since 1.1.0
						 */
						do_action( 'profiles_profile_field_item' ); ?>

					<?php endwhile; ?>

				</table>
			</div>

			<?php

			/** This action is documented in profiles-templates/profiles-legacy/profiles/members/single/profile/profile-wp.php */
			do_action( 'profiles_after_profile_field_content' ); ?>

		<?php endif; ?>

	<?php endwhile; ?>

	<?php

	/** This action is documented in profiles-templates/profiles-legacy/profiles/members/single/profile/profile-wp.php */
	do_action( 'profiles_profile_field_buttons' ); ?>

<?php endif; ?>

<?php

/** This action is documented in profiles-templates/profiles-legacy/profiles/members/single/profile/profile-wp.php */
do_action( 'profiles_after_profile_loop_content' ); ?>
