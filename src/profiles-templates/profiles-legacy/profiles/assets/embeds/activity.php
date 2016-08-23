
		<?php if ( profiles_activity_embed_has_activity( profiles_current_action() ) ) : ?>

			<?php while ( profiles_activities() ) : profiles_the_activity(); ?>
				<div class="profiles-embed-excerpt"><?php profiles_activity_embed_excerpt(); ?></div>

				<?php profiles_activity_embed_media(); ?>

			<?php endwhile; ?>

		<?php endif; ?>
