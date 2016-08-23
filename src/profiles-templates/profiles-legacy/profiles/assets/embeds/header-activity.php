
		<div id="profiles-embed-header">
			<div class="profiles-embed-avatar">
				<a href="<?php profiles_displayed_user_link(); ?>">
					<?php profiles_displayed_user_avatar( 'type=thumb&width=45&height=45' ); ?>
				</a>
			</div>

			<?php if ( profiles_activity_embed_has_activity( profiles_current_action() ) ) : ?>

				<?php while ( profiles_activities() ) : profiles_the_activity(); ?>
					<p class="profiles-embed-activity-action">
						<?php profiles_activity_action( array( 'no_timestamp' => true ) ); ?>
					</p>
				<?php endwhile; ?>

			<?php endif; ?>

			<p class="profiles-embed-header-meta">

				<span class="profiles-embed-timestamp"><a href="<?php profiles_activity_thread_permalink(); ?>"><?php echo date_i18n( get_option( 'time_format' ) . ' - ' . get_option( 'date_format' ), strtotime( profiles_get_activity_date_recorded() ) ); ?></a></span>
			</p>
		</div>
