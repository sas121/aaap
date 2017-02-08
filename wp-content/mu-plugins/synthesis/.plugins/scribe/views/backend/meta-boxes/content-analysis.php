<div class="scribe-analysis-meta-box-container">
	<div class="scribe-analysis-meta-box-results misc-pub-section">
		<div class="scribe-analysis-meta-box-doc-score-container">
			<div data-content-analysis-score="<?php echo esc_attr( $content_analysis->docScore ); ?>" class="scribe-content-analysis-score scribe-analysis-meta-box-score <?php echo scribe_score_class( $content_analysis->docScore ); ?>">
				<?php 
				if ( empty( $content_analysis ) ) {
					 esc_html_e( '-', 'scribeseo' );
				} elseif ( isset( $content_analysis->docScore ) ) {
					printf( '%d', $content_analysis->docScore );
				} else {
					esc_html_e( 'N/A', 'scribeseo' );
				}
				?>
			</div>
			<h4><?php esc_html_e( 'Page Score', 'scribeseo' ); ?></h4>
		</div>
		<div class="scribe-analysis-meta-box-site-score-container">
			<div data-content-analysis-score="<?php echo esc_attr( $content_analysis->scribeScore ); ?>" class="scribe-content-analysis-score scribe-analysis-meta-box-score <?php echo scribe_score_class( $content_analysis->scribeScore, 'site' ); ?>">
				<?php
				if ( empty( $content_analysis ) ) {
					 esc_html_e( '-', 'scribeseo' );
				} elseif ( isset( $content_analysis->scribeScore ) ) {
					printf( '%d', $content_analysis->scribeScore );
				} else {
					esc_html_e( 'N/A', 'scribeseo' );
				}
				?>
			</div>
			<h4><?php esc_html_e( 'Site Score', 'scribeseo' ); ?></h4>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="scribe-analysis-meta-box-dependencies misc-pub-section">
		<ul>
			<li>
				<span class="scribe-analysis-dependency-title">
					<?php esc_html_e('Title Tag Needed', 'scribeseo'); ?>
				</span>
				<span class="scribe-analysis-dependency-title scribe-ready">
					<?php esc_html_e('Title Tag Ready', 'scribeseo'); ?>
				</span>
			</li>
			<li>
				<span class="scribe-analysis-dependency-description">
					<?php esc_html_e('Meta Description Needed', 'scribeseo'); ?>
				</span>
				<span class="scribe-analysis-dependency-description scribe-ready">
					<?php esc_html_e('Meta Description Ready', 'scribeseo'); ?>
				</span>
			</li>
			<li>
				<span class="scribe-analysis-dependency-content">
					<?php esc_html_e('Content', 'scribeseo'); ?>
				</span>
				<span class="scribe-analysis-dependency-content scribe-ready">
					<?php esc_html_e('Content Ready', 'scribeseo'); ?>
				</span>
			</li>
		</ul>
	</div>
	
	<div class="scribe-analysis-meta-box-statistics misc-pub-section">
		<?php esc_html_e('Evaluations left: ', 'scribeseo'); ?><strong><span class="scribe-analysis-evaluations-remaining"><?php echo number_format_i18n($remaining_evaluations); ?></span> <?php printf( esc_html__('as of %s', 'scribeseo'), date('F j, Y')); ?></strong>
	</div>
	
	<div class="scribe-analysis-meta-box-actions">
		<div class="scribe-analysis-meta-box-review-action alignleft" <?php echo empty( $content_analysis ) ? ' style="display:none"' : ''; ?>>
			<input title="<?php esc_html_e('Content Analysis', 'scribeseo'); ?>" id="scribe-content-analysis-review-button" type="button" class="button button-secondary thickbox" alt="<?php scribe_the_upload_iframe_src('scribe-analysis-keywords', null, array('scribe-content-analysis-review' => 'true')); ?>" value="<?php esc_html_e('Review', 'scribeseo'); ?>" />
		</div>
		
		<div class="scribe-analysis-meta-box-analyze-action alignright">
			<?php
			if ( Scribe_SEO::get_content_analysis_evaluations_remaining() ) { 
			?>
			<img alt="" id="scribe-analysis-is-analyzing" class="ajax-loading" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>">
			<input id="scribe-content-analysis-analyze-button" type="button" class="button button-primary" value="<?php esc_html_e('Analyze', 'scribeseo'); ?>" />
			<?php 
				wp_nonce_field('scribe-analyze-content', 'scribe-analyze-content-nonce');

			}
		?>
			<span class="scribe-content-analysis-out-of-evals scribe-out-of-evals"><?php _e( 'Out of evaluations', 'scribeseo' ); ?></span>
			
		</div>
		<div class="clear"></div>
	</div>
</div>