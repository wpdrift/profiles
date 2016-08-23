<?php
/**
 * Profiles Avatars camera template.
 *
 * This template is used to create the camera Backbone views.
 *
 * @since 2.3.0
 *
 * @package Profiles
 * @suprofilesackage profiles-attachments
 */

?>
<script id="tmpl-profiles-avatar-webcam" type="text/html">
	<# if ( ! data.user_media ) { #>
		<div id="profiles-webcam-message">
			<p class="warning"><?php esc_html_e( 'Your browser does not support this feature.', 'profiles' );?></p>
		</div>
	<# } else { #>
		<div id="avatar-to-crop"></div>
		<div class="avatar-crop-management">
			<div id="avatar-crop-pane" class="avatar" style="width:{{data.w}}px; height:{{data.h}}px"></div>
			<div id="avatar-crop-actions">
				<a class="button avatar-webcam-capture" href="#"><?php esc_html_e( 'Capture', 'profiles' );?></a>
				<a class="button avatar-webcam-save" href="#"><?php esc_html_e( 'Save', 'profiles' );?></a>
			</div>
		</div>
	<# } #>
</script>
