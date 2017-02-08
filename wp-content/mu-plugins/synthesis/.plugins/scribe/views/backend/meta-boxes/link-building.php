
<div class="scribe-link-building-meta-box-results scribe-link-building-meta-box-section">
	<ul>
		<li>
			<span class="scribe-link-building-dependency-content-analysis">
				<?php esc_html_e('Perform Content Analysis on the Content to enable Link Building.', 'scribeseo'); ?>
			</span>
			<span class="scribe-link-building-dependency-content-analysis scribe-ready">
				<?php esc_html_e('Content analyzed. Link building ready.', 'scribeseo'); ?>
			</span>
		</li>
		<li class="scribe-link-building-link-term scribe-info">
			<strong><?php esc_html_e('Link Term', 'scribeseo'); ?></strong>: 
			<span class="scribe-link-building-link-term-text">
				<select name="scribe-link-building-term" id="scribe-link-building-term">
					<option value=""><?php esc_html_e( '-- Select Term --', 'scribeseo' ); ?></option>
					<?php
					foreach( (array)$link_terms_array as $term ) {
						printf( '<option value="%s">%s</option>', urlencode( $term ), ucwords( esc_html( $term ) ) );
					}
					?>
				</select>
				<input type="hidden" id="scribe-link-building-term-list" value="<?php echo esc_html( $link_terms ); ?>" />
			</span>
		</li>
	</ul>
	<div class="scribe-link-building-review-button-container alignleft">
		<input title="<?php esc_html_e('Link Building', 'scribeseo'); ?>" type="button" class="scribe-link-building-review-button button thickbox" alt="<?php scribe_the_upload_iframe_src('scribe-link-building', null, array('scribe-link-building-review' => 1)); ?>" value="<?php esc_html_e('Review', 'scribeseo'); ?>" />	
	</div>
	<div class="scribe-link-building-research-button-container alignright">
		<div class="alignleft">
			<img id="scribe-keyword-research-ajax-feedback" alt="" title="" class="scribe-ajax-feedback" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" style="visibility: hidden;">
		</div>
		<input title="<?php esc_html_e('Link Building', 'scribeseo'); ?>" type="button" data-content-analysis-score="<?php echo esc_attr( $content_score ); ?>" class="scribe-content-analysis-score scribe-link-building-research-button button button-primary thickbox" alt="<?php scribe_the_upload_iframe_src('scribe-link-building'); ?>" value="<?php esc_html_e('Research', 'scribeseo'); ?>" />
		<span class="scribe-link-building-out-of-evals scribe-out-of-evals"><?php _e( 'Out of evaluations', 'scribeseo' ); ?></span>
	</div>
	<div class="clear"></div>
</div>