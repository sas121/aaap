<div class="scribe-wrap">
	<div class="scribe-link-building-tabs" data-link-building-complete="<?php echo esc_attr( $link_building_complete ); ?>">
		<h3 class="nav-tab-wrapper scribe-link-building-tabs-identifiers">
			<a class="nav-tab" href="#scribe-link-building-difficulty" id="scribe-link-building-difficulty-tab-identifier"><?php esc_html_e('Difficulty Score', 'scribeseo'); ?></a>
			<a class="nav-tab" href="#scribe-link-building-external-links" id="scribe-link-building-external-links-tab-identifier" <?php echo scribe_get_link_building_tab_load( $link_building_info, 'ext' ); ?>><?php esc_html_e('External Links', 'scribeseo'); ?></a>
			<a class="nav-tab" href="#scribe-link-building-internal-links" id="scribe-link-building-internal-links-tab-identifier" <?php echo scribe_get_link_building_tab_load( $link_building_info, 'int' ); ?>><?php esc_html_e('Internal Links', 'scribeseo'); ?></a>
			<a class="nav-tab" href="#scribe-link-building-social-media" id="scribe-link-building-social-media-tab-identifier" <?php echo scribe_get_link_building_tab_load( $link_building_info, 'soc' ); ?>><?php esc_html_e('Social Media', 'scribeseo'); ?></a>
		</h3>
	</div>

	<div class="scribe-link-building-tabs scribe-link-building-keyword-section">

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
	<div class="scribe-link-building-tabs" data-link-building-complete="<?php echo $link_building_complete; ?>">
		<div class="scribe-link-building-tab-section" id="scribe-link-building-difficulty">
			<a class="scribe-tip-marker scribe-show-tip" href="#">+</a>
			<a class="scribe-tip-marker scribe-hide-tip scribe-link-building-tip" href="#">-</a>
			<div class="scribe-link-building-tip">
				<p><strong>It’s Time to Become the “Likeable Expert” in Your Niche</strong></p>

				<p>The human brain is wired to seek out authority. One powerful way authority
				is demonstrated online is through <em>who’s linking to your content</em>.</p>

				<p>Use <em>Scribe’s Difficulty Score</em> to determine how likely it is that
				another online publisher will link to you—the higher your score, the
				harder it will be earn external links … the lower your score, the easier.</p>

				<p><a href="http://www.copyblogger.com/content-marketing/" target="_blank">Click here</a> to
				study a few useful tutorials that’ll help you write the kind of content
				your audience is looking for.</p>
			</div>
			<h3 class="scribe-link-building-tab-difficulty-header"><?php printf( esc_html__( 'Link Difficulty Score for %s', 'scribeseo' ), implode( ' + ', (array) $link_building_info->keywords ) ); ?></h3>
			
			<div class="scribe-link-building-tab-difficulty-score-container scribe-popup-analysis-score-wrap">
				<div class="scribe-link-building-tab-difficulty-score <?php echo scribe_bar_graph_class( $link_building_info->linkScore, 'difficulty' ); ?>" style="float: left; width: 30%"><span><?php echo isset( $link_building_info->linkScore ) && is_numeric( $link_building_info->linkScore ) ? (int) $link_building_info->linkScore : 0; ?></span></div>
				<span class="scribe-wrap scribe-link-building-tab-difficulty-score-text" style="float: left; width: 55%"><?php echo esc_html( $link_building_info->score_description ); ?></span>
				<div class="clear"></div>
			</div>
			
			<?php echo esc_html( $score_help_text ); ?>
		</div>
		<div class="scribe-link-building-tab-section" id="scribe-link-building-external-links">
			<a class="scribe-tip-marker scribe-show-tip" href="#">+</a>
			<a class="scribe-tip-marker scribe-hide-tip scribe-link-building-tip" href="#">-</a>
			<div class="scribe-link-building-tip">
				<p><strong>Two Simple Strategies for Building External Links to Your Website</strong></p>

				<p>Links coming to your site from other authoritative sites are crucial to
				your ranking in search engines. But how do you get those links when nobody
				knows who you are?</p>

				<p><em>Scribe’s External Links function</em> shows you—and even gives you
				the available <em>contact information</em> of— websites that are ranking
				well for the keywords in your post.</p>

				<p>Use it to execute two simple and powerful strategies for building external
				links: 1) Head over to one of the sites and <a href="
				http://www.copyblogger.com/blog-comment-traffic/" target="_blank">leave a relevant, smart
				comment</a>, and 2) Contact the site owner with an <a href="
				http://www.copyblogger.com/successful-guest-blogging/" target="_blank">offer to guest
				post</a> for them, using the very best content you’ve written.</p>
			</div>
			<table class="widefat" class="scribe-link-building-external-links-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e('URL', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Page Authority', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Contact Name', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Telephone', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e( 'Links', 'scribeseo' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col"><?php esc_html_e('URL', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Page Authority', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Contact Name', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Telephone', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e( 'Links', 'scribeseo' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<tr id="scribe-link-building-external-links-row-placeholder">
						<td class="scribe-link-building-external-links-row-url"><?php esc_html_e('Not Available', 'scribeseo'); ?></td>
						<td class="scribe-link-building-external-links-row-domain-authority"><?php esc_html_e('Not Available', 'scribeseo'); ?></td>
						<td>
							<span class="scribe-link-building-external-links-row-contact-name"><?php esc_html_e('Not Available', 'scribeseo'); ?></span>
						</td>
						<td class="scribe-link-building-external-links-row-telephone"><?php esc_html_e('Not Available', 'scribeseo'); ?></td>
						<td class="scribe-link-building-external-links-row-links"><?php esc_html_e('Not Available', 'scribeseo'); ?></td>
					</tr>
					<?php 
					if ( isset( $link_building_info->externalLinks ) ) {
						foreach( $link_building_info->externalLinks as $external) {
					?>
					<tr>
						<td class="scribe-link-building-external-links-row-url">
							<?php 
							if ( empty( $external->url ) ) {
								esc_html_e( '-', 'scribseo');
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $external->url ), esc_html( parse_url( $external->url, PHP_URL_HOST ) ) );
							}
							?>
						</td>
						<td class="scribe-link-building-external-links-row-domain-authority">
							<?php 
							if ( empty( $external->url ) ) {
								esc_html_e( '-', 'scribeseo');
							} else {
								printf( '<a href="http://www.opensiteexplorer.org/links?site=%s">%.0f</a>', esc_url( $external->url ), $external->pageAuthority );
							}
							?>
						</td>
						<td class="scribe-link-building-external-links-row-contact-name">
							<?php 
							if ( ! empty( $external->name ) ) {
								if ( ! empty( $external->email ) ) {
									printf( '<a href="mailto:%s">%s</a>', esc_attr( $external->email ), esc_html( $external->name ) );
								} else {
									echo esc_html( $external->name );
								}
							} elseif ( ! empty( $external->email ) ) {
								printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $external->email ) );
							} else {
								esc_html_e( '-', 'scribeseo');
							}
							?>
						</td>
						<td class="scribe-link-building-external-links-row-telephone">
							<?php echo empty( $external->telephone ) ? esc_html__( '-', 'scribseo' , 'scribeseo') : esc_html( $external->telephone ); ?>
						</td>
						<td class="scribe-link-building-external-links-row-links">
							<?php echo empty( $external->numberOfPagesToUrl ) ? esc_html__( '-', 'scribseo' , 'scribeseo') : (int) $external->numberOfPagesToUrl; ?>
						</td>
					</tr>
					<?php } } ?>
				</tbody>
			</table>
		</div>
		<div class="scribe-link-building-tab-section" id="scribe-link-building-internal-links">
			<a class="scribe-tip-marker scribe-show-tip" href="#">+</a>
			<a class="scribe-tip-marker scribe-hide-tip scribe-link-building-tip" href="#">-</a>
			<div class="scribe-link-building-tip">
				<p><strong>How to Make Your Best Content a Workhorse That Doesn’t Quit</strong></p>

				<p>Like linking to external websites, you can (and should) regularly use the
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
			<table class="widefat" class="scribe-link-building-internal-links-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e('Page Title', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Page Authority', 'scribeseo'); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col"><?php esc_html_e('Page Title', 'scribeseo'); ?></th>
						<th scope="col"><?php esc_html_e('Page Authority', 'scribeseo'); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<tr id="scribe-link-building-internal-links-row-none" <?php echo empty( $link_building_info->internalLinks ) ? '' : 'style="display:none;"'; ?>>
						<td class="scribe-link-building-internal-links-row-page-title"><?php esc_html_e( 'No results found. Please check your Scribe Settings or retry your research.', 'scribeseo' ); ?></td>
						<td class="scribe-link-building-internal-links-row-page-authority"></td>
					</tr>
					<tr id="scribe-link-building-internal-links-row-placeholder">
						<td class="scribe-link-building-internal-links-row-page-title"><a href="https://my.scribeseo.com/optimizer/post-internal-links.aspx?kwds=XXXX&amp;url=<?php echo urlencode('http://'); ?>YYYY" target="_blank"></a></td>
						<td class="scribe-link-building-internal-links-row-page-authority"></td>
					</tr>
					<?php 
					if ( isset( $link_building_info->internalLinks ) ) {
						$keywords_imploded = urlencode( implode(',', $content_analysis_keywords ) );
						foreach( $link_building_info->internalLinks as $internal ) {
					?>
					<tr>
						<td class="scribe-link-building-internal-links-row-page-title"><a href="https://my.scribeseo.com/optimizer/post-internal-links.aspx?kwds=<?php echo $keywords_imploded; ?>&amp;url=<?php echo urlencode( $internal->url ); ?>" target="_blank"><?php echo esc_html( $internal->pageTitle ); ?></a></td>
						<td class="scribe-link-building-internal-links-row-page-authority"><?php printf( '%.0f', $internal->pageAuthority ); ?></td>
					</tr>	
					<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
		<div class="scribe-link-building-tab-section" id="scribe-link-building-social-media">
			<a class="scribe-tip-marker scribe-show-tip" href="#">+</a>
			<a class="scribe-tip-marker scribe-hide-tip scribe-link-building-tip" href="#">-</a>
			<div class="scribe-link-building-tip">
				<p><strong>Harness the Nearly Unlimited Networking Power of Social Networks</strong></p>

				<p>The rise of social networking sites like Twitter, Facebook and Google+ has
				created the most powerful research and connection engine in history. With
				just a few clicks, you can read the innermost thoughts of the famous, or
				(more importantly) the desires, needs, and fears of your potential
				audience.</p>

				<p>But how do you make sense of this firehose of information? The <em>Scribe
				Social Media function</em> does a lot of that for you.</p>

				<p>Based on the keywords of your post, Scribe will seek out and display the
				relevant people and the discussions they’re having right now, so you can <a
				href="http://www.copyblogger.com/social-media-networking/" target="_blank">join the
				conversation</a> and <a href="
				http://www.copyblogger.com/social-media-relationships/" target="_blank">engage your
				audience</a>.</p>
			</div>
			<div id="scribe-keyword-suggestions-google-plus-widget-container">
			</div>
		</div>
	</div>
	
	<ul>
		<li id="scribe-link-building-keyword-list-template" class="scribe-link-building-keyword scribe-link-building-added-keyword"><span class="scribe-link-building-keyword-term"></span><a href="#" class="scribe-link-building-keyword-list-remove">x</a></li>
	</ul>
</div>
