<div class="scribe-wrap">
	<div class="scribe-document-analysis-search-result-previews-container">
		<h3><?php esc_html_e('Page Analysis Metric', 'scribeseo'); ?></h3>
		<div class="scribe-document-analysis-keyword-analysis-metric-outer scribe-popup-analysis-score-wrap">
			<div class="scribe-wrap scribe-content-analysis-score scribe-analysis-meta-box-score" style="float: left; width: 40%">
				<?php esc_html_e( 'Page Score: ', 'scribeseo' ); ?>
				<span class="<?php echo scribe_score_class( $content_analysis->docScore ); ?>">
				<?php
				if ( empty( $content_analysis ) ) {
					 esc_html_e( '-', 'scribeseo' );
				} elseif ( isset( $content_analysis->docScore ) ) {
					printf( '%d', $content_analysis->docScore );
				} else {
					esc_html_e( 'N/A', 'scribeseo' );
				}
				?>
				</span>
			</div>
			<span class="scribe-wrap" style="float: left; width: 45%"><?php echo scribe_get_content_analysis_doc_score_text( $content_analysis->docScore, $content_analysis->keywords ); ?></span>
			<div class="clear"></div>
		</div>
	</div>

	<div class="scribe-document-analysis-improve-document-structure">
		<h3><?php esc_html_e('Improve Page Structure', 'scribeseo'); ?></h3>
		<p><?php esc_html_e('To improve the overall structure of your page, review the recommendations below.', 'scribeseo'); ?></p>

		<table class="widefat scribe-document-analysis-improve-document-structure-table">
			<thead>
				<tr valign="top">
					<th scope="col"><?php esc_html_e('Analysis &amp; Recommendations', 'scribeseo'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $recommendations = self::get_content_recommendations($content_analysis->docScoreE); ?>
				<tr>
					<td>
						<ul <?php echo empty( $recommendations ) ? 'class="scribe-ready"' : ''; ?> style="margin: 0;">
						<?php if(empty($recommendations)) { ?>
							<li><span><?php esc_html_e('Good job! There are no recommendations for your content.', 'scribeseo'); ?></span></li>
						<?php
						} else {
							foreach($recommendations as $recommendation) {
						?>
							<li><span><?php echo esc_html($recommendation); ?></span></li>
						<?php
							}
						}
						?>
							<li><span>
								<strong><?php esc_html_e('Flesch Score', 'scribeseo'); ?></strong>
								<?php printf( esc_html__( 'The Flesch Score describes comprehension difficulty for a passage of text.<br />The Flesch score for this page is: %s', 'scribeseo'), '<strong>' . esc_html( ucwords( $content_analysis->fleschScore ) ) . '</strong>' ); ?>
							</span></li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="scribe-document-analysis-search-result-previews-container">
		<h3><?php esc_html_e( 'Search Engine Example', 'scribseo' , 'scribeseo'); ?></h3>
		<p><?php esc_html_e('The following is an example of  how the content may appear in a Bing or Google search result.  You may make changes to this by editing the Title Tag and Meta Description.', 'scribeseo'); ?></p>

		<?php
		foreach((array)$content_analysis->serps as $engine_info) {
			if ( 'google' != $engine_info->engine )
				continue;
		?>
		<div class="scribe-document-analysis-search-result-preview-container scribe-document-analysis-search-result-preview-container-<?php echo esc_attr( $engine_info->engine ); ?>">
			<div class="scribe-document-analysis-search-result-preview-outer scribe-document-analysis-search-result-preview-outer-<?php echo esc_attr( $engine_info->engine ); ?>">
				<div class="scribe-document-analysis-search-result-preview scribe-document-analysis-search-result-preview-<?php echo esc_attr( $engine_info->engine ); ?>">
					<p><a target="_blank" href="<?php echo esc_url( $serp_url ? $serp_url : $content_analysis->serp_url ); ?>" class="scribe-document-analysis-search-result-preview-title"><?php echo esc_html($content_analysis->serp_title); ?></a></p>
					
					<p class="scribe-document-analysis-search-result-preview-url"><?php echo esc_html( $serp_url ? $serp_url : $content_analysis->serp_url ); ?></p>
					<p class="scribe-document-analysis-search-result-preview-description"><?php echo esc_html( $content_analysis->serp_description ); ?></p>
					
				</div>
			</div>
		</div>
		<?php } ?>

	</div>
	
	<div class="scribe-document-analysis-improve-keyword-grades">
		<h3><?php esc_html_e('Improve Keyword Rank', 'scribeseo'); ?></h3>
		<p><?php esc_html_e('To improve the ranking of your keywords, review the recommendations below.', 'scribeseo'); ?></p>
		
		<table class="widefat scribe-document-analysis-improve-keyword-grades-table">
			<thead>
				<tr valign="top">
					<th scope="col" class="scribe-document-analysis-improve-keyword-grades-table-keyword"><?php esc_html_e('Keyword', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-document-analysis-improve-keyword-grades-table-recommendations"><?php esc_html_e('Analysis & Recommendations', 'scribeseo'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach( $content_analysis->keywords as $keyword ) {
					$keyword_recommendations = self::get_keyword_recommendations( $keyword->kwe, $keyword->text );
					if ( empty( $keyword_recommendations ) )
						continue; 
				?>
				<tr valign="top">
					<td scope="row" class="scribe-document-analysis-improve-keyword-grades-table-keyword"><?php echo esc_html( ucwords( $keyword->text ) ); ?></td>
					<td class="scribe-document-analysis-improve-keyword-grades-table-recommendations">
						<ul style="margin: 0;">
							<?php foreach($keyword_recommendations as $recommendation) { ?>
							<li><span><?php echo esc_html( $recommendation ); ?></span></li>
							<?php } ?>
						</ul>
					</td>
				</tr>
				<?php } ?>
				<tr valign="top">
					<td class="scribe-document-analysis-excluded-keywords-recommendations" colspan="2">
					<?php
						if ( ! empty( $content_analysis->excludes ) ) {

							$excludes = '<strong>' . ucwords( implode( ', ', $content_analysis->excludes ) ) . '</strong>';
							printf( __( 'Note, the following term(s) are excluded from content analysis: %s. If you want to include these term(s) in the content analysis, please use the Target Term feature of Scribe Keyword Research.', 'scribeseo' ), $excludes );

						}
					?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>