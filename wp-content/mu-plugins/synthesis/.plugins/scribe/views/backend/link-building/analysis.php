<div class="scribe-wrap">
	<div class="scribe-link-building-tabs scribe-link-building-keyword-section">
			<div class="scribe-link-building-tip-visible">
				<p><strong>How to Make Your Best Content a Workhorse That Doesn’t Quit</strong></p>

				<p>Like linking to external websites (using the <a href="<?php echo admin_url( 'admin.php?page=scribe-connections' ); ?>" target="_blank">Scribe Site Connector</a> tool), you can (and should) regularly use the
				authority of your own best content to link <em>internally</em>, to your own
				pages.

				<p>What should you link to? Again, to your best content — what we call your
				<em>cornerstone content</em>. Your best advice, your best thinking, and
				your best answers to the questions your audience comes up with again and
				again.

				<p>Use <em>Scribe’s Internal Links function</em> to find and <a href="
				http://www.copyblogger.com/seo-site-quality/" target="_blank">link to that best content on
				your own site</a>, and make it work <em>for you</em> again and again.</p>
			</div>

	<?php wp_nonce_field('scribe-build-links', 'scribe-build-links-nonce'); ?>
	<input type="hidden" name="post_ID" id="post_ID" value="<?php echo esc_attr( $post_id ); ?>" />
	<table class="form-table">
		<tbody>
			<tr class="scribe-link-building-keyword-select-row">
				<th scope="row"><label for="scribe-link-building-keywords"><?php esc_html_e('Select Keywords:', 'scribeseo'); ?></label></th>
				<td>
					<select name="scribe-link-building-keywords" id="scribe-link-building-keywords">
						<option value=""><?php esc_html_e('Select from the list', 'scribeseo'); ?></option>
						<?php foreach( $content_analysis_keywords as $keyword ) { ?>
						<option <?php if( in_array( ucwords( $keyword ), $link_building_keywords ) ) { ?> style="display: none;" <?php } ?> value="<?php echo esc_attr( ucwords( $keyword ) ); ?>"><?php echo esc_html( ucwords( $keyword ) ); ?></option>
						<?php } ?>
					</select>
					<input type="button" class="button button-secondary" id="scribe-link-building-add-keyword" value="<?php esc_html_e('Add Keyword', 'scribeseo'); ?>" />
				</td>
			</tr>
			<tr class="scribe-link-building-keyword-row">
				<th scope="row"><?php esc_html_e('Keyword List:', 'scribeseo'); ?></th>
				<td>
					<span id="scribe-link-building-keyword-list-empty"><?php esc_html_e('Please add some keywords.', 'scribeseo'); ?></span>
					<ul id="scribe-link-building-keyword-list">
						<?php
						if ( ! empty( $link_building_keywords ) ) {
							foreach( $link_building_keywords as $link_building_info_keyword ) {
								if ( ! trim( $link_building_info_keyword ) )
									continue;
						?>
						<li class="scribe-link-building-keyword"><span class="scribe-link-building-keyword-term <?php echo ( ucwords( $initial_term ) == $link_building_info_keyword ? 'scribe-link-building-added-keyword' : '' ); ?>"><?php echo esc_html( $link_building_info_keyword ); ?></span><a href="#" class="scribe-link-building-keyword-list-remove">x</a></li>
						<?php
							}
						}
						?>
					</ul>
					<div class="clear"></div>
					<div class="scribe-link-building-research-submit">
						<input type="button" class="button button-primary" name="scribe-link-building-do-link-building-research" id="scribe-link-building-do-link-building-research" value="<?php esc_html_e('Research', 'scribeseo'); ?>" />
						<img id="scribe-build-links-ajax-feedback" alt="" title="" class="scribe-ajax-feedback" src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" style="visibility: hidden;">
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	</div>

	<div class="scribe-link-building-tabs" data-link-building-complete="<?php echo esc_attr( $link_building_complete ); ?>">
		<div id="scribe-link-building-internal-links">
			<table class="widefat" class="scribe-link-building-internal-links-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Page Title', 'scribeseo' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Page Authority', 'scribeseo' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Links', 'scribeseo' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col"><?php esc_html_e( 'Page Title', 'scribeseo' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Page Authority', 'scribeseo' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Links', 'scribeseo' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<tr id="scribe-link-building-internal-links-row-none" <?php echo empty( $link_building_info->internalLinks ) ? '' : 'style="display:none;"'; ?>>
						<td class="scribe-link-building-internal-links-row-page-title"><?php esc_html_e( 'No results found. Please check your Scribe Settings or retry your research.', 'scribeseo' ); ?></td>
						<td class="scribe-link-building-internal-links-row-page-authority"></td>
						<td class="scribe-link-building-internal-links-row-page-links"></td>
					</tr>
					<tr id="scribe-link-building-internal-links-row-placeholder">
						<td class="scribe-link-building-internal-links-row-page-title"><a href="http://supportfiles.scribeseo.com/post-internal-links.aspx?kwds=XXXX&amp;url=<?php echo urlencode('http://'); ?>YYYY" target="_blank"></a></td>
						<td class="scribe-link-building-internal-links-row-page-authority"></td>
						<td class="scribe-link-building-internal-links-row-page-links"></td>
					</tr>
					<?php 
					if ( isset( $link_building_info->internalLinks ) ) {
						$keyword_list = ! empty( $link_building_info->keywords ) ? implode( ',', $link_building_info->keywords ) : $target_term;
						$keywords_imploded = urlencode( $keyword_list );
						foreach( $link_building_info->internalLinks as $internal ) {
					?>
					<tr>
						<td class="scribe-link-building-internal-links-row-page-title"><a href="http://supportfiles.scribeseo.com/post-internal-links.aspx?kwds=<?php echo $keywords_imploded; ?>&amp;url=<?php echo urlencode( $internal->url ); ?>" target="_blank"><?php echo esc_html( $internal->pageTitle ); ?></a></td>
						<td class="scribe-link-building-internal-links-row-page-authority"><?php printf( '%.0f', $internal->pageAuthority ); ?></td>
						<td class="scribe-link-building-internal-links-row-page-links"><?php echo number_format( $internal->numberOfPagesToUrl, 0 ); ?></td>
					</tr>	
					<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
	<ul>
		<li id="scribe-link-building-keyword-list-template" class="scribe-link-building-keyword scribe-link-building-added-keyword"><span class="scribe-link-building-keyword-term"></span><a href="#" class="scribe-link-building-keyword-list-remove">x</a></li>
	</ul>
</div>
