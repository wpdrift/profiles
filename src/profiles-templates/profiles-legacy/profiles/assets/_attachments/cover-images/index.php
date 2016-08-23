<?php
/**
 * Profiles Cover Images main template.
 *
 * This template is used to inject the Profiles Backbone views
 * dealing with cover images.
 *
 * It's also used to create the common Backbone views.
 *
 * @since 2.4.0
 *
 * @package Profiles
 * @suprofilesackage profiles-attachments
 */

?>

<div class="profiles-cover-image"></div>
<div class="profiles-cover-image-status"></div>
<div class="profiles-cover-image-manage"></div>

<?php profiles_attachments_get_template_part( 'uploader' ); ?>

<script id="tmpl-profiles-cover-image-delete" type="text/html">
	<# if ( 'user' === data.object ) { #>
		<p><?php _e( "If you'd like to delete your current cover image but not upload a new one, please use the delete Cover Image button.", 'profiles' ); ?></p>
		<p><a class="button edit" id="profiles-delete-cover-image" href="#"><?php esc_html_e( 'Delete My Cover Image', 'profiles' ); ?></a></p>
	<# } else if ( 'group' === data.object ) { #>
		<p><?php _e( "If you'd like to remove the existing group cover image but not upload a new one, please use the delete group cover image button.", 'profiles' ); ?></p>
		<p><a class="button edit" id="profiles-delete-cover-image" href="#"><?php esc_html_e( 'Delete Group Cover Image', 'profiles' ); ?></a></p>
	<# } else { #>
		<?php do_action( 'profiles_attachments_cover_image_delete_template' ); ?>
	<# } #>
</script>

<?php do_action( 'profiles_attachments_cover_image_main_template' ); ?>
