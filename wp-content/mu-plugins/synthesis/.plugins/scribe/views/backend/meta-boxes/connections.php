<div class="scribe-wrap">
	<div class="scribe-link-building-tabs" data-link-building-complete="false">
		<div class="nav-tab-wrapper scribe-link-building-tabs-identifiers">
			<a class="nav-tab" href="#scribe-link-building-difficulty" id="scribe-link-building-difficulty-tab-identifier"><?php esc_html_e( 'Site Score', 'scribeseo' ); ?></a>
			<a class="nav-tab" href="#scribe-link-building-external-links" id="scribe-link-building-external-links-tab-identifier" <?php echo scribe_get_link_building_tab_load( null, 'ext' ); ?>><?php esc_html_e('External Links', 'scribeseo'); ?></a>
			<a class="nav-tab" href="#scribe-link-building-social-media" id="scribe-link-building-social-media-tab-identifier" <?php echo scribe_get_link_building_tab_load( null, 'soc' ); ?>><?php esc_html_e('Social Media', 'scribeseo'); ?></a>
		</div>
	</div>

	<div class="scribe-link-building-tabs scribe-link-building-keyword-section">

	<?php wp_nonce_field('scribe-build-links', 'scribe-build-links-nonce'); ?>
	<input type="hidden" name="post_ID" id="post_ID" value="<?php echo esc_attr( $post_id ); ?>" />
			<div class="scribe-link-building-keyword-select-row">
				<span><label for="scribe-link-building-keywords"><?php esc_html_e('Keywords:', 'scribeseo'); ?></label></span>
				<span>
					<input type="text" name="scribe-link-building-keywords" id="scribe-site-connections-keywords">
				</span>
				<span class="alignright scribe-site-connections-research-submit">
						<input type="button" class="button button-primary" name="scribe-link-building-do-site-connections-research" id="scribe-link-building-do-site-connections-research" value="<?php esc_html_e('Research', 'scribeseo'); ?>" />
						<img id="scribe-build-links-ajax-feedback" alt="" title="" class="scribe-ajax-feedback" src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" style="visibility: hidden;">
				</span>
				<div class="clear"></div>
			</div>
	</div>
	<div class="scribe-link-building-tabs" data-link-building-complete="false">
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
			<h3 class="scribe-link-building-tab-difficulty-header"></h3>
			
			<div class="scribe-link-building-tab-difficulty-score-container scribe-popup-analysis-score-wrap">
				<div class="scribe-link-building-tab-difficulty-score" style="float: left; width: 30%"><span></span></div>
				<span class="scribe-wrap scribe-link-building-tab-difficulty-score-text" style="float: left; width: 55%"></span>
				<div class="clear"></div>
			</div>
			
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
</div>
