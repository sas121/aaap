<div class="scribe-wrap">
	<div id="scribe-tags-added-message" class="updated"><p><?php esc_html_e( 'The tags you have selected will be added when you save this post.', 'scribeseo' ); ?></p></div>
	<form method="post" action="<?php echo add_query_arg( array() ); ?>">
		<p><?php esc_html_e('Consider adding the following semantically relevant terms to your Post Tags.', 'scribeseo'); ?></p>
		<ul>
			<?php if(empty($content_analysis->tags)) { ?>
				<li><?php esc_html_e('No tags were found during the content analysis.', 'scribeseo'); ?></li>
			<?php } else { ?>
				<?php foreach( $tags as $tag => $checked ) { ?>
				<li>
					<label>
						<input type="checkbox" class="scribe-post-tag" name="scribe-post-tags[]" value="<?php echo esc_attr( ucwords( $tag ) ); ?>" <?php checked( $checked ); ?> />
						<?php echo esc_html( ucwords( $tag ) ); ?>
					</label>
				</li>
				<?php } ?>
			<?php } ?>
		</ul>
		<p class="submit">
			<input class="button button-primary" type="submit" name="scribe-add-post-tags" id="scribe-add-post-tags" value="<?php esc_html_e('Add to Post Tags', 'scribeseo'); ?>" />
		</p>
	</form>
</div>
