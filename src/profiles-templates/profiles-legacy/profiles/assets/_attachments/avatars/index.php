<?php
/**
 * Profiles Avatars main template.
 *
 * This template is used to inject the Profiles Backbone views
 * dealing with avatars.
 *
 * It's also used to create the common Backbone views.
 *
 * @since 2.3.0
 *
 * @package Profiles
 * @suprofilesackage profiles-attachments
 */

/**
 * This action is for internal use, please do not use it
 */
do_action( 'profiles_attachments_avatar_check_template' );
?>
<div class="profiles-avatar-nav"></div>
<div class="profiles-avatar"></div>
<div class="profiles-avatar-status"></div>

<script type="text/html" id="tmpl-profiles-avatar-nav">
	<a href="{{data.href}}" class="profiles-avatar-nav-item" data-nav="{{data.id}}">{{data.name}}</a>
</script>

<?php profiles_attachments_get_template_part( 'uploader' ); ?>

<?php profiles_attachments_get_template_part( 'avatars/crop' ); ?>

<?php profiles_attachments_get_template_part( 'avatars/camera' ); ?>

<script id="tmpl-profiles-avatar-delete" type="text/html">
	<# if ( 'user' === data.object ) { #>
		<p><?php _e( "If you'd like to delete your current profile photo but not upload a new one, please use the delete profile photo button.", 'profiles' ); ?></p>
		<p><a class="button edit" id="profiles-delete-avatar" href="#"><?php esc_html_e( 'Delete My Profile Photo', 'profiles' ); ?></a></p>
	<# } else if ( 'group' === data.object ) { #>
		<p><?php _e( "If you'd like to remove the existing group profile photo but not upload a new one, please use the delete group profile photo button.", 'profiles' ); ?></p>
		<p><a class="button edit" id="profiles-delete-avatar" href="#"><?php esc_html_e( 'Delete Group Profile Photo', 'profiles' ); ?></a></p>
	<# } else { #>
		<?php do_action( 'profiles_attachments_avatar_delete_template' ); ?>
	<# } #>
</script>

<?php do_action( 'profiles_attachments_avatar_main_template' ); ?>
