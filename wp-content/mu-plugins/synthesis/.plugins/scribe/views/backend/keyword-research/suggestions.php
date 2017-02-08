<div class="scribe-wrap">
	<?php if(is_wp_error($keywords)) { ?>
	<p><?php printf( esc_html__('An error ocurred when retrieving keyword suggestions for the term you entered. Please %s and try again.', 'scribeseo'), '<a class="scribe-thickbox-close" href="#">' . esc_html__( 'close this pop up', 'scribeseo' ) . '</a>' ); ?></p>
	<?php } else { ?>
	
	<div class="scribe-link-building-tabs" data-link-building-complete="true">
		<h3 class="nav-tab-wrapper scribe-link-building-tabs-identifiers">
			<a class="nav-tab nav-tab-active" href="#scribe-keyword-research-suggestions" id="scribe-keyword-research-suggestions-tab-identifier"><?php esc_html_e('Keyword Suggestions', 'scribeseo'); ?></a>
			<a class="nav-tab" href="#scribe-keyword-research-google-plus" id="scribe-keyword-research-google-plus-tab-identifier"><?php esc_html_e('Social Media', 'scribeseo'); ?></a>
			<a class="nav-tab" href="#scribe-keyword-research-google-insights" id="scribe-keyword-research-google-insights-tab-identifier"><?php esc_html_e('Google Trends', 'scribeseo'); ?></a>
		</h3>
		
		<div class="scribe-link-building-tab-section" id="scribe-keyword-research-suggestions">
			<?php if( $previous ) { ?>
			<p style="margin-top: 0;"><?php esc_html_e('This information is historical and may not be accurate.', 'scribeseo'); ?></p>
			<?php } ?>
			
			<table class="scribe-keyword-suggestions widefat fixed">
				<thead>
					<tr valign="top">
						<th class="scribe-keyword-suggestions-target" scope="col">
							<?php esc_html_e('Target', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#" data-placement="right" rel="popover" data-content="<?php esc_attr_e( 'A target keyword will be included in the Content Analysis of Scribe. You may select one target keyword.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Target', 'scribeseo' ); ?>">?</a>
						</th>
						<th class="scribe-keyword-suggestions-keyword" scope="col">
							<?php esc_html_e('Keywords', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#"  rel="popover" data-content="<?php esc_attr_e( 'A list of recommended keywords for your consideration based on the keyword you entered.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Keywords', 'scribeseo' ); ?>">?</a>
						</th>
						<th class="scribe-keyword-suggestions-popularity" scope="col">
							<?php esc_html_e('Popularity', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#"  rel="popover" data-content="<?php esc_attr_e( 'Grades the overall popularity of a term. The higher the score, the more popular the term.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Popularity', 'scribeseo' ); ?>">?</a>
						</th>
						<th class="scribe-keyword-suggestions-competition" scope="col">
							<?php esc_html_e('Competition %', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#" data-placement="left" rel="popover" data-content="<?php esc_attr_e( 'The number of pages on the web that are targeting the keyword from an SEO perspective. The higher the number, the more competitive the term.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Competition', 'scribeseo' ); ?>">?</a>
						</th>
						<?php if($previous) { ?>
						<th class="scribe-keyword-suggestions-date" scope="col">
							<?php esc_html_e('Date', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#" data-placement="left" rel="popover" data-content="<?php esc_attr_e( 'The date when the keyword list was last generated.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Date', 'scribeseo' ); ?>">?</a>
						</th>
						<?php } ?>
					</tr>
				</thead>
				<tfoot>
					<tr valign="top">
						<th class="scribe-keyword-suggestions-target" scope="col">
							<?php esc_html_e('Target', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#" data-placement="right" rel="popover" data-content="<?php esc_attr_e( 'A target keyword will be included in the Content Analysis of Scribe. You may select one target keyword.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Target', 'scribeseo' ); ?>">?</a>
						</th>
						<th class="scribe-keyword-suggestions-keyword" scope="col">
							<?php esc_html_e('Keywords', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#"  rel="popover" data-content="<?php esc_attr_e( 'A list of recommended keywords for your consideration based on the keyword you entered.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Keywords', 'scribeseo' ); ?>">?</a>
						</th>
						<th class="scribe-keyword-suggestions-popularity" scope="col">
							<?php esc_html_e('Popularity', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#"  rel="popover" data-content="<?php esc_attr_e( 'Grades the overall popularity of a term. The higher the score, the more popular the term.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Popularity', 'scribeseo' ); ?>">?</a>
						</th>
						<th class="scribe-keyword-suggestions-competition" scope="col">
							<?php esc_html_e('Competition %', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#" data-placement="left" rel="popover" data-content="<?php esc_attr_e( 'The number of pages on the web that are targeting the keyword from an SEO perspective. The higher the number, the more competitive the term.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Competition', 'scribeseo' ); ?>">?</a>
						</th>
						<?php if($previous) { ?>
						<th class="scribe-keyword-suggestions-date" scope="col">
							<?php esc_html_e('Date', 'scribeseo' ); ?>
							<a class="scribe-help-marker" href="#" data-placement="left" rel="popover" data-content="<?php esc_attr_e( 'The date when the keyword list was last generated.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Date', 'scribeseo' ); ?>">?</a>
						</th>
						<?php } ?>
					</tr>
				</tfoot>
				<tbody>
				<?php 
				if ( empty( $keywords ) ) {
				?>
					<tr valign="top">
						<?php if($previous) { ?>
						<td colspan="6"><?php wp_kses( __( 'You don\'t currently have any saved keyword searches. Please <a class="scribe-thickbox-close" href="#">close this pop up</a> and do some keyword research.', 'scribeseo'), Scribe_SEO::formatting_allowedtags() ); ?></td>
						<?php } else { ?>
						<td colspan="6"><?php esc_html_e('There are no keyword suggestions available for this keyword.', 'scribeseo'); ?></td>	
						<?php } ?>
					</tr>
				<?php 
				} else {

					foreach($keywords as $keyword) {

						$detail_url = scribe_get_upload_iframe_src( 'scribe-keyword-suggestions',
							'scribe-keyword-details',
							array(
								'scribe-keyword' => $queried_keyword,
								'scribe-details-keyword' => $keyword->term,
								'scribe-details-previous' => $previous,
								'scribe-research-term' => $previous_research_term
							)
						);
				?>
					<tr valign="top">
						<th class="scribe-keyword-suggestions-target" scope="row">
							<input <?php checked($target_term, $keyword->term); ?> type="radio" name="scribe-keyword-suggestions-target" value="<?php echo esc_attr($keyword->term); ?>" />
						</th>
						<td class="scribe-keyword-suggestions-keyword">
							<a rel="twipsy" title="<?php esc_attr_e('More Details', 'scribeseo'); ?>" href="<?php echo esc_url( $detail_url ); ?>"><?php echo esc_html( $keyword->term ); ?></a>
						</td>
						<td class="scribe-keyword-suggestions-popularity">
							<div class="scribe-keyword-details-graph-outer">
								<div class="scribe-keyword-details-graph">
									<?php $score = min( $keyword->popularity, 100 ); ?>
									<div class="scribe-keyword-details-graph-bar <?php echo scribe_bar_graph_class( $score, 'popularity' ); ?>" style="width: <?php echo esc_attr( max( $score, 10 ) ); ?>%;">
										<div class="scribe-keyword-details-graph-score"><?php echo esc_html( $score ); ?></div>
									</div>
								</div>
							</div>
						</td>
						<td class="scribe-keyword-suggestions-competition">
							<div class="scribe-keyword-details-graph-outer">
								<div class="scribe-keyword-details-graph">
									<div class="scribe-keyword-details-graph-bar <?php echo scribe_bar_graph_class( $keyword->competition, 'competition' ); ?>" style="width: <?php echo esc_attr( max( $keyword->competition, 10 ) ); ?>%;">
										<div class="scribe-keyword-details-graph-score scribe-keyword-details-competition-score">
											<?php printf(' %d', $keyword->competition); ?>
										</div>
									</div>
								</div>
							</div>
						</td>
						<?php if($previous) { ?>
						<td class="scribe-keyword-suggestions-date"><?php echo date('F j, Y', $keyword->time_retrieved); ?></td>
						<?php } ?>
					</tr>
					<?php } ?>
				<?php } ?>
				</tbody>
			</table>
			
			<?php if(!empty($keywords)) { ?>
			<div id="scribe-keyword-suggestions-set-target-success" class="updated hide-if-js"><p><strong><?php esc_html_e('Your keyword, will be included in your Content Analysis process.', 'scribeseo'); ?></strong> (<a href="#" class="scribe-thickbox-close"><?php esc_html_e('close', 'scribeseo'); ?></a>)</p></div>
			<div class="clear"></div>
			
			<div class="alignleft" id="scribe-keyword-suggestions-set-target-container">
				<?php wp_nonce_field('scribe-set-target-term', 'scribe-set-target-term-nonce'); ?>
				
				<p style="margin: 0 0 5px;"><?php esc_html_e('By selecting a Target Term above and clicking the Save Target Term button, your keyword will be included in the Content Analysis process.', 'scribeseo'); ?></p>
				<input class="button button-primary" data-post-id="<?php echo esc_attr( $_GET['post_id'] ); ?>" type="button" id="scribe-keyword-suggestions-set-target" name="scribe-keyword-suggestions-set-target" value="<?php esc_attr_e('Save Target Term', 'scribeseo'); ?>" />
				<img id="scribe-keyword-research-set-target-term-ajax-feedback" alt="" title="" class="scribe-ajax-feedback" src="<?php esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" style="visibility: hidden;">
		
				<span class="scribe-keyword-research-clear-target-term-container">
					(<a class="scribe-clear-target-term" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'scribe_clear_target_term', 'scribe-post-id' => (int)$_GET['post_id'] ), admin_url( 'admin-ajax.php' ) ), 'scribe-clear-target-term' ); ?>"><?php esc_html_e( 'clear', 'scribeseo' ); ?></a>)
				</span>
			</div>
			<?php } ?>
		</div>
		
		<div class="scribe-link-building-tab-section" id="scribe-keyword-research-google-plus">
			<?php wp_nonce_field('scribe-build-links', 'scribe-build-links-nonce'); ?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="scribe-keyword-research-google-plus-headlines-term"><?php esc_html_e('Headline Suggestions for', 'scribeseo'); ?></label></th>
						<td>
							<select class="scribe-keyword-research-headlines-term-select scribe-select-new scribe-select-changed" id="scribe-keyword-research-google-plus-headlines-term">
								<?php foreach( $keywords as $keyword ) { ?>
								<option value="<?php echo esc_attr( $keyword->term ); ?>" <?php selected( $keyword->term, $queried_keyword ); ?>><?php echo esc_html( $keyword->term ); ?></option>
								<?php } ?>
							</select>
							<a class="scribe-help-marker" href="#" data-placement="left" rel="popover" data-content="<?php esc_attr_e( 'Find out what people are saying for this keyword.', 'scribeseo' ); ?>" title="<?php esc_attr_e('Google+', 'scribeseo' ); ?>">?</a>
						</td>
						<td>
							<img id="scribe-google-plus-ajax-feedback" alt="" title="" class="scribe-ajax-feedback" src="<?php  echo esc_url(admin_url('images/wpspin_light.gif')); ?>" style="visibility: hidden;">
						</td>
					</tr>
				</tbody>
			</table>
			
			<div id="scribe-keyword-suggestions-google-plus-widget-container">
			</div>
		</div>
		
		<div class="scribe-link-building-tab-section" id="scribe-keyword-research-google-insights">
			<?php wp_nonce_field('scribe-research-google-trends', 'scribe-google-trends-nonce'); ?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="scribe-keyword-research-google-insights-term"><?php esc_html_e('Trends for', 'scribeseo'); ?></label></th>
						<td>
							<select class="scribe-keyword-research-headlines-term-select scribe-select-new js-on-load" id="scribe-keyword-research-google-insights-headlines-term">
								<?php foreach($keywords as $keyword) { ?>
								<option value="<?php echo esc_attr( $keyword->term ); ?>" <?php selected( $keyword->term, $queried_keyword ); ?>><?php echo esc_html( $keyword->term ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div id="scribe-keyword-suggestions-google-insights-widget-container">
				<br />
				<div id="scribe-keyword-details-gtrends">
				</div>
			</div>
		</div>
	</div>

	<?php } ?>
</div>

