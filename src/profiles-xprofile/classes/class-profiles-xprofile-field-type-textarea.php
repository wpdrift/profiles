<?php
/**
 * Profiles XProfile Classes.
 *
 * @package Profiles
 * @suprofilesackage XProfileClasses
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Textarea xprofile field type.
 *
 * @since 2.0.0
 */
class Profiles_XProfile_Field_Type_Textarea extends Profiles_XProfile_Field_Type {

	/**
	 * Constructor for the textarea field type.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->category = _x( 'Single Fields', 'xprofile field type category', 'profiles' );
		$this->name     = _x( 'Multi-line Text Area', 'xprofile field type', 'profiles' );
		$this->supports_richtext = true;

		$this->set_format( '/^.*$/m', 'replace' );

		/**
		 * Fires inside __construct() method for Profiles_XProfile_Field_Type_Textarea class.
		 *
		 * @since 2.0.0
		 *
		 * @param Profiles_XProfile_Field_Type_Textarea $this Current instance of
		 *                                              the field type textarea.
		 */
		do_action( 'profiles_xprofile_field_type_textarea', $this );
	}

	/**
	 * Output the edit field HTML for this field type.
	 *
	 * Must be used inside the {@link profiles_profile_fields()} template loop.
	 *
	 * @since 2.0.0
	 *
	 * @param array $raw_properties Optional key/value array of
	 *                              {@link http://dev.w3.org/html5/markup/textarea.html permitted attributes}
	 *                              that you want to add.
	 */
	public function edit_field_html( array $raw_properties = array() ) {

		// User_id is a special optional parameter that certain other fields
		// types pass to {@link profiles_the_profile_field_options()}.
		if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$richtext_enabled = profiles_xprofile_is_richtext_enabled_for_field(); ?>

		<label for="<?php profiles_the_profile_field_input_name(); ?>">
			<?php profiles_the_profile_field_name(); ?>
			<?php profiles_the_profile_field_required_label(); ?>
		</label>

		<?php

		/** This action is documented in profiles-xprofile/profiles-xprofile-classes */
		do_action( profiles_get_the_profile_field_errors_action() );

		if ( ! $richtext_enabled ) {
			$r = wp_parse_args( $raw_properties, array(
				'cols' => 40,
				'rows' => 5,
			) );

			?>

			<textarea <?php echo $this->get_edit_field_html_elements( $r ); ?>><?php profiles_the_profile_field_edit_value(); ?></textarea>

			<?php

		} else {

			/**
			 * Filters the arguments passed to `wp_editor()` in richtext xprofile fields.
			 *
			 * @since 2.4.0
			 *
			 * @param array $args {
			 *     Array of optional arguments. See `wp_editor()`.
			 *     @type bool $teeny         Whether to use the teeny version of TinyMCE. Default true.
			 *     @type bool $media_buttons Whether to show media buttons. Default false.
			 *     @type bool $quicktags     Whether to show the quicktags buttons. Default true.
			 *     @type int  $textarea_rows Number of rows to display in the editor. Defaults to 1 in the
			 *                               'admin' context, and 10 in the 'edit' context.
			 * }
			 * @param string $context The display context. 'edit' when the markup is intended for the
			 *                        profile edit screen, 'admin' when intended for the Profile Fields
			 *                        Dashboard panel.
			 */
			$editor_args = apply_filters( 'profiles_xprofile_field_type_textarea_editor_args', array(
				'teeny'         => true,
				'media_buttons' => false,
				'quicktags'     => true,
				'textarea_rows' => 10,
			), 'edit' );

			wp_editor(
				profiles_get_the_profile_field_edit_value(),
				profiles_get_the_profile_field_input_name(),
				$editor_args
			);
		}
	}

	/**
	 * Output HTML for this field type on the wp-admin Profile Fields screen.
	 *
	 * Must be used inside the {@link profiles_profile_fields()} template loop.
	 *
	 * @since 2.0.0
	 *
	 * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
	 */
	public function admin_field_html( array $raw_properties = array() ) {
		$richtext_enabled = profiles_xprofile_is_richtext_enabled_for_field();

		if ( ! $richtext_enabled ) {

			$r = profiles_parse_args( $raw_properties, array(
				'cols' => 40,
				'rows' => 5,
			) ); ?>

			<textarea <?php echo $this->get_edit_field_html_elements( $r ); ?>></textarea>

			<?php
		} else {

			/** This filter is documented in profiles-xprofile/classes/class-profiles-xprofile-field-type-textarea.php */
			$editor_args = apply_filters( 'profiles_xprofile_field_type_textarea_editor_args', array(
				'teeny'         => true,
				'media_buttons' => false,
				'quicktags'     => true,
				'textarea_rows' => 1,
			), 'admin' );

			wp_editor(
				'',
				'xprofile_textarea_' . profiles_get_the_profile_field_id(),
				$editor_args
			);
		}
	}

	/**
	 * This method usually outputs HTML for this field type's children options on the wp-admin Profile Fields
	 * "Add Field" and "Edit Field" screens, but for this field type, we don't want it, so it's stubbed out.
	 *
	 * @since 2.0.0
	 *
	 * @param Profiles_XProfile_Field $current_field The current profile field on the add/edit screen.
	 * @param string            $control_type  Optional. HTML input type used to render the current
	 *                                         field's child options.
	 */
	public function admin_new_field_html( Profiles_XProfile_Field $current_field, $control_type = '' ) {}
}
