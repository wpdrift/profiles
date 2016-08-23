<?php
/**
 * Profiles XProfile Admin Class.
 *
 * @package Profiles
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Profiles_XProfile_User_Admin' ) ) :

/**
 * Load xProfile Profile admin area.
 *
 * @since 2.0.0
 */
class Profiles_XProfile_User_Admin {

	/**
	 * Setup xProfile User Admin.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function register_xprofile_user_admin() {

		// Bail if not in admin.
		if ( ! is_admin() ) {
			return;
		}

		$profiles = profiles();

		if ( empty( $profiles->profile->admin ) ) {
			$profiles->profile->admin = new self;
		}

		return $profiles->profile->admin;
	}

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Set admin-related actions and filters.
	 *
	 * @since 2.0.0
	 */
	private function setup_actions() {
		// Enqueue scripts.
		add_action( 'profiles_members_admin_enqueue_scripts',  array( $this, 'enqueue_scripts'    ), 10, 1 );

		// Register the metabox in Member's community admin profile.
		add_action( 'profiles_members_admin_xprofile_metabox', array( $this, 'register_metaboxes' ), 10, 3 );

		// Saves the profile actions for user ( avatar, profile fields ).
		add_action( 'profiles_members_admin_update_user',      array( $this, 'user_admin_load'    ), 10, 4 );
	}

	/**
	 * Enqueue needed scripts.
	 *
	 * @since 2.3.0
	 *
	 * @param int $screen_id Screen ID being displayed.
	 */
	public function enqueue_scripts( $screen_id ) {
		if ( ( false === strpos( $screen_id, 'users_page_profiles-profile-edit' )
			&& false === strpos( $screen_id, 'profile_page_profiles-profile-edit' ) )
			|| profiles_core_get_root_option( 'profiles-disable-avatar-uploads' )
			|| ! profiles()->avatar->show_avatars
			|| ! profiles_attachments_is_wp_version_supported() ) {
			return;
		}

		/**
		 * Get Thickbox.
		 *
		 * We cannot simply use add_thickbox() here as WordPress is not playing
		 * nice with Thickbox width/height see https://core.trac.wordpress.org/ticket/17249
		 * Using media-upload might be interesting in the future for the send to editor stuff
		 * and we make sure the tb_window is wide enougth
		 */
		wp_enqueue_style ( 'thickbox' );
		wp_enqueue_script( 'media-upload' );

		// Get Avatar Uploader.
		profiles_attachments_enqueue_scripts( 'Profiles_Attachment_Avatar' );
	}

	/**
	 * Register the xProfile metabox on Community Profile admin page.
	 *
	 * @since 2.0.0
	 *
	 * @param int         $user_id       ID of the user being edited.
	 * @param string      $screen_id     Screen ID to load the metabox in.
	 * @param object|null $stats_metabox Context and priority for the stats metabox.
	 */
	public function register_metaboxes( $user_id = 0, $screen_id = '', $stats_metabox = null ) {

		// Set the screen ID if none was passed.
		if ( empty( $screen_id ) ) {
			$screen_id = profiles()->members->admin->user_page;
		}

		// Setup a new metabox class if none was passed.
		if ( empty( $stats_metabox ) ) {
			$stats_metabox = new StdClass();
		}

		// Moving the Stats Metabox.
		$stats_metabox->context  = 'side';
		$stats_metabox->priority = 'low';

		// Each Group of fields will have his own metabox.
		$profile_args = array(
			'fetch_fields' => false,
			'user_id'      => $user_id,
		);

		if ( ! profiles_is_user_spammer( $user_id ) && profiles_has_profile( $profile_args ) ) {

			// Loop through field groups and add a metabox for each one.
			while ( profiles_profile_groups() ) : profiles_the_profile_group();
				add_meta_box(
					'profiles_xprofile_user_admin_fields_' . sanitize_key( profiles_get_the_profile_group_slug() ),
					esc_html( profiles_get_the_profile_group_name() ),
					array( $this, 'user_admin_profile_metaboxes' ),
					$screen_id,
					'normal',
					'core',
					array( 'profile_group_id' => absint( profiles_get_the_profile_group_id() ) )
				);
			endwhile;


		} else {
			// If member is already a spammer, show a generic metabox.
			add_meta_box(
				'profiles_xprofile_user_admin_empty_profile',
				_x( 'User marked as a spammer', 'xprofile user-admin edit screen', 'profiles' ),
				array( $this, 'user_admin_spammer_metabox' ),
				$screen_id,
				'normal',
				'core'
			);
		}

		if ( profiles()->avatar->show_avatars ) {
			// Avatar Metabox.
			add_meta_box(
				'profiles_xprofile_user_admin_avatar',
				_x( 'Profile Photo', 'xprofile user-admin edit screen', 'profiles' ),
				array( $this, 'user_admin_avatar_metabox' ),
				$screen_id,
				'side',
				'low'
			);
		}
	}

	/**
	 * Save the profile fields in Members community profile page.
	 *
	 * Loaded before the page is rendered, this function is processing form
	 * requests.
	 *
	 * @since 2.0.0
	 *
	 * @param string $doaction    Action being run.
	 * @param int    $user_id     ID for the user whose profile is being saved.
	 * @param array  $request     Request being made.
	 * @param string $redirect_to Where to redirect user to.
	 */
	public function user_admin_load( $doaction = '', $user_id = 0, $request = array(), $redirect_to = '' ) {

		// Eventually delete avatar.
		if ( 'delete_avatar' === $doaction ) {

			check_admin_referer( 'delete_avatar' );

			$redirect_to = remove_query_arg( '_wpnonce', $redirect_to );

			if ( profiles_core_delete_existing_avatar( array( 'item_id' => $user_id ) ) ) {
				$redirect_to = add_query_arg( 'updated', 'avatar', $redirect_to );
			} else {
				$redirect_to = add_query_arg( 'error', 'avatar', $redirect_to );
			}

			profiles_core_redirect( $redirect_to );

		} elseif ( isset( $_POST['field_ids'] ) ) {
			// Update profile fields.
			// Check the nonce.
			check_admin_referer( 'edit-profiles-profile_' . $user_id );

			// Check we have field ID's.
			if ( empty( $_POST['field_ids'] ) ) {
				$redirect_to = add_query_arg( 'error', '1', $redirect_to );
				profiles_core_redirect( $redirect_to );
			}

			/**
			 * Unlike front-end edit-fields screens, the wp-admin/profile
			 * displays all groups of fields on a single page, so the list of
			 * field ids is an array gathering for each group of fields a
			 * distinct comma separated list of ids.
			 *
			 * As a result, before using the wp_parse_id_list() function, we
			 * must ensure that these ids are "merged" into a single comma
			 * separated list.
			 */
			$merge_ids = join( ',', $_POST['field_ids'] );

			// Explode the posted field IDs into an array so we know which fields have been submitted.
			$posted_field_ids = wp_parse_id_list( $merge_ids );
			$is_required      = array();

			// Loop through the posted fields formatting any datebox values then validate the field.
			foreach ( (array) $posted_field_ids as $field_id ) {
				if ( ! isset( $_POST['field_' . $field_id ] ) ) {
					if ( ! empty( $_POST['field_' . $field_id . '_day'] ) && ! empty( $_POST['field_' . $field_id . '_month'] ) && ! empty( $_POST['field_' . $field_id . '_year'] ) ) {

						// Concatenate the values.
						$date_value =   $_POST['field_' . $field_id . '_day'] . ' ' . $_POST['field_' . $field_id . '_month'] . ' ' . $_POST['field_' . $field_id . '_year'];

						// Turn the concatenated value into a timestamp.
						$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
					}
				}

				$is_required[ $field_id ] = xprofile_check_is_required_field( $field_id ) && ! profiles_current_user_can( 'profiles_moderate' );
				if ( $is_required[ $field_id ] && empty( $_POST['field_' . $field_id ] ) ) {
					$redirect_to = add_query_arg( 'error', '2', $redirect_to );
					profiles_core_redirect( $redirect_to );
				}
			}

			// Set the errors var.
			$errors = false;

			// Now we've checked for required fields, let's save the values.
			$old_values = $new_values = array();
			foreach ( (array) $posted_field_ids as $field_id ) {

				/*
				 * Certain types of fields (checkboxes, multiselects) may come
				 * through empty. Save them as an empty array so that they don't
				 * get overwritten by the default on the next edit.
				 */
				$value = isset( $_POST['field_' . $field_id] ) ? $_POST['field_' . $field_id] : '';

				$visibility_level = ! empty( $_POST['field_' . $field_id . '_visibility'] ) ? $_POST['field_' . $field_id . '_visibility'] : 'public';
				/*
				 * Save the old and new values. They will be
				 * passed to the filter and used to determine
				 * whether an activity item should be posted.
				 */
				$old_values[ $field_id ] = array(
					'value'      => xprofile_get_field_data( $field_id, $user_id ),
					'visibility' => xprofile_get_field_visibility_level( $field_id, $user_id ),
				);

				// Update the field data and visibility level.
				xprofile_set_field_visibility_level( $field_id, $user_id, $visibility_level );
				$field_updated = xprofile_set_field_data( $field_id, $user_id, $value, $is_required[ $field_id ] );
				$value         = xprofile_get_field_data( $field_id, $user_id );

				$new_values[ $field_id ] = array(
					'value'      => $value,
					'visibility' => xprofile_get_field_visibility_level( $field_id, $user_id ),
				);

				if ( ! $field_updated ) {
					$errors = true;
				} else {

					/**
					 * Fires after the saving of each profile field, if successful.
					 *
					 * @since 1.1.0
					 *
					 * @param int    $field_id ID of the field being updated.
					 * @param string $value    Value that was saved to the field.
					 */
					do_action( 'xprofile_profile_field_data_updated', $field_id, $value );
				}
			}

			/**
			 * Fires after all XProfile fields have been saved for the current profile.
			 *
			 * @since 1.0.0
			 * @since 2.6.0 Added $old_values and $new_values parameters.
			 *
			 * @param int   $user_id          ID for the user whose profile is being saved.
			 * @param array $posted_field_ids Array of field IDs that were edited.
			 * @param bool  $errors           Whether or not any errors occurred.
			 * @param array $old_values       Array of original values before update.
			 * @param array $new_values       Array of newly saved values after update.
			 */
			do_action( 'xprofile_updated_profile', $user_id, $posted_field_ids, $errors, $old_values, $new_values );

			// Set the feedback messages.
			if ( ! empty( $errors ) ) {
				$redirect_to = add_query_arg( 'error',   '3', $redirect_to );
			} else {
				$redirect_to = add_query_arg( 'updated', '1', $redirect_to );
			}

			profiles_core_redirect( $redirect_to );
		}
	}

	/**
	 * Render the xprofile metabox for Community Profile screen.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User|null $user The WP_User object for the user being edited.
	 * @param array        $args Aray of arguments for metaboxes.
	 */
	public function user_admin_profile_metaboxes( $user = null, $args = array() ) {

		// Bail if no user ID.
		if ( empty( $user->ID ) ) {
			return;
		}

		$r = profiles_parse_args( $args['args'], array(
			'profile_group_id' => 0,
			'user_id'          => $user->ID
		), 'profiles_xprofile_user_admin_profile_loop_args' );

		// We really need these args.
		if ( empty( $r['profile_group_id'] ) || empty( $r['user_id'] ) ) {
			return;
		}

		// Bail if no profile fields are available.
		if ( ! profiles_has_profile( $r ) ) {
			return;
		}

		// Loop through profile groups & fields.
		while ( profiles_profile_groups() ) : profiles_the_profile_group(); ?>

			<input type="hidden" name="field_ids[]" id="<?php echo esc_attr( 'field_ids_' . profiles_get_the_profile_group_slug() ); ?>" value="<?php echo esc_attr( profiles_get_the_profile_group_field_ids() ); ?>" />

			<?php if ( profiles_get_the_profile_group_description() ) : ?>

				<p class="description"><?php profiles_the_profile_group_description(); ?></p>

			<?php endif; ?>

			<?php while ( profiles_profile_fields() ) : profiles_the_profile_field(); ?>

				<div<?php profiles_field_css_class( 'profiles-profile-field' ); ?>>

					<?php

					$field_type = profiles_xprofile_create_field_type( profiles_get_the_profile_field_type() );
					$field_type->edit_field_html( array( 'user_id' => $r['user_id'] ) );

					if ( profiles_get_the_profile_field_description() ) : ?>

						<p class="description"><?php profiles_the_profile_field_description(); ?></p>

					<?php endif;

					/**
					 * Fires before display of visibility form elements for profile metaboxes.
					 *
					 * @since 1.7.0
					 */
					do_action( 'profiles_custom_profile_edit_fields_pre_visibility' );

					$can_change_visibility = profiles_current_user_can( 'profiles_xprofile_change_field_visibility' ); ?>

					<p class="field-visibility-settings-<?php echo $can_change_visibility ? 'toggle' : 'notoggle'; ?>" id="field-visibility-settings-toggle-<?php profiles_the_profile_field_id(); ?>">

						<?php
						printf(
							__( 'This field can be seen by: %s', 'profiles' ),
							'<span class="current-visibility-level">' . profiles_get_the_profile_field_visibility_level_label() . '</span>'
						);
						?>

						<?php if ( $can_change_visibility ) : ?>

							<a href="#" class="button visibility-toggle-link"><?php esc_html_e( 'Change', 'profiles' ); ?></a>

						<?php endif; ?>
					</p>

					<?php if ( $can_change_visibility ) : ?>

						<div class="field-visibility-settings" id="field-visibility-settings-<?php profiles_the_profile_field_id() ?>">
							<fieldset>
								<legend><?php _e( 'Who can see this field?', 'profiles' ); ?></legend>

								<?php profiles_profile_visibility_radio_buttons(); ?>

							</fieldset>
							<a class="button field-visibility-settings-close" href="#"><?php esc_html_e( 'Close', 'profiles' ); ?></a>
						</div>

					<?php endif; ?>

					<?php

					/**
					 * Fires at end of custom profile field items on your xprofile screen tab.
					 *
					 * @since 1.1.0
					 */
					do_action( 'profiles_custom_profile_edit_fields' ); ?>

				</div>

			<?php endwhile; // End profiles_profile_fields(). ?>

		<?php endwhile; // End profiles_profile_groups.
	}

	/**
	 * Render the fallback metabox in case a user has been marked as a spammer.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User|null $user The WP_User object for the user being edited.
	 */
	public function user_admin_spammer_metabox( $user = null ) {
	?>
		<p><?php printf( __( '%s has been marked as a spammer. All Profiles data associated with the user has been removed', 'profiles' ), esc_html( profiles_core_get_user_displayname( $user->ID ) ) ) ;?></p>
	<?php
	}

	/**
	 * Render the Avatar metabox to moderate inappropriate images.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User|null $user The WP_User object for the user being edited.
	 */
	public function user_admin_avatar_metabox( $user = null ) {

		if ( empty( $user->ID ) ) {
			return;
		} ?>

		<div class="avatar">

			<?php echo profiles_core_fetch_avatar( array(
				'item_id' => $user->ID,
				'object'  => 'user',
				'type'    => 'full',
				'title'   => $user->display_name
			) ); ?>

			<?php if ( profiles_get_user_has_avatar( $user->ID ) ) :

				$query_args = array(
					'user_id' => $user->ID,
					'action'  => 'delete_avatar'
				);

				if ( ! empty( $_REQUEST['wp_http_referer'] ) ) {
					$query_args['wp_http_referer'] = urlencode( wp_unslash( $_REQUEST['wp_http_referer'] ) );
				}

				$community_url = add_query_arg( $query_args, profiles()->members->admin->edit_profile_url );
				$delete_link   = wp_nonce_url( $community_url, 'delete_avatar' ); ?>

				<a href="<?php echo esc_url( $delete_link ); ?>" class="profiles-xprofile-avatar-user-admin"><?php esc_html_e( 'Delete Profile Photo', 'profiles' ); ?></a>

			<?php endif;

			// Load the Avatar UI templates if user avatar uploads are enabled and current WordPress version is supported.
			if ( ! profiles_core_get_root_option( 'profiles-disable-avatar-uploads' ) && profiles_attachments_is_wp_version_supported() ) : ?>
				<a href="#TB_inline?width=800px&height=400px&inlineId=profiles-xprofile-avatar-editor" class="thickbox profiles-xprofile-avatar-user-edit"><?php esc_html_e( 'Edit Profile Photo', 'profiles' ); ?></a>
				<div id="profiles-xprofile-avatar-editor" style="display:none;">
					<?php profiles_attachments_get_template_part( 'avatars/index' ); ?>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

}
endif; // End class_exists check.
