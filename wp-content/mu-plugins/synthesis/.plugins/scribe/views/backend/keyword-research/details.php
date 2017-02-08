<div class="scribe-wrap">
	
	<?php if ( is_wp_error( $details ) ) { ?>
	
	<p><?php echo esc_html( $details->get_error_message() ); ?></p>
	
	<?php } else { ?>
	
	<?php if ( isset( $details->cached ) && $_GET['scribe-details-previous'] ) { ?>
	<div class="scribe-keyword-details-old-warning"><?php esc_html_e( 'This information is historical and may not be currently accurate.', 'scribeseo' ); ?></div>
	<?php } ?>
	
	<div class="scribe-keyword-details-overall-container">
		<div class="scribe-keyword-details-overall">
			<div class="scribe-keyword-details-overall-score <?php echo scribe_bar_graph_class( $details->scoreDifficulty, 'keyword-difficulty' ); ?>">
				<?php echo esc_html( $details->scoreDifficulty ); ?>
			</div>
			<div class="scribe-keyword-details-overall-score-others">
				<h3 class="scribe-keyword-details-overall-score-header"><?php printf( esc_html__( 'Overall Score for: %s', 'scribeseo' ), '<span class="scribe-keyword-details-overall-score-header-term">' . esc_html( $queried_keyword ) . '</span>' ); ?></h3>
				<p><?php scribe_the_overall_score_description( $details->scoreDifficulty ); ?></p>
			</div>
			
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="scribe-keyword-details-analysis scribe-keyword-details-section">
		<div class="scribe-keyword-details-graph-container">
			<h4><?php esc_html_e('Content Score', 'scribeseo'); ?></h4>
			<div class="scribe-keyword-details-graph-symbol scribe-keyword-details-graph-symbol-cross"></div>
			<div class="scribe-keyword-details-graph-outer">
				<div class="scribe-keyword-details-graph">
					<div class="scribe-keyword-details-graph-bar <?php echo scribe_bar_graph_class( $details->scoreContent ); ?>" style="width: <?php echo esc_attr( min( $details->scoreContent, 100 ) ); ?>%;">
						<div class="scribe-keyword-details-graph-score"><?php echo ( $details->scoreContent <= 100 ? (int)$details->scoreContent : '100+' ); ?></div>
					</div>
				</div>
			</div>
			<p class="scribe-keyword-details-graph-description"><?php scribe_the_content_score_description( $details->scoreContent ); ?> <?php printf( '<a data-placement="%s" rel="popover" title="%4$s" data-content="%2$s" href="%3$s">%4$s &raquo;</a>', 'right', esc_html__( 'The Content Score compares the amount of content on your site for the term versus the amount of content for sites that rank for the term. A score of 100 indicates that your site content is on par with those ranking sites.', 'scribeseo' ), scribe_get_content_score_more_link( $details->scoreContent ), esc_html__( 'Tell me more', 'scribeseo' ) ); ?></p>
		</div>
		
		<div class="scribe-keyword-details-graph-container alt">
			<h4><?php esc_html_e('Link Score', 'scribeseo'); ?></h4>
			<div class="scribe-keyword-details-graph-symbol scribe-keyword-details-graph-symbol-check"></div>
			<div class="scribe-keyword-details-graph-outer">
				<div class="scribe-keyword-details-graph">
					<div class="scribe-keyword-details-graph-bar <?php echo scribe_bar_graph_class( $details->scoreLinks ); ?>" style="width: <?php echo esc_attr( min( $details->scoreLinks, 100 ) ); ?>%;">
						<div class="scribe-keyword-details-graph-score"><?php echo ( $details->scoreLinks <= 100 ? (int)$details->scoreLinks : '100+' ); ?></div>
					</div>
				</div>
			</div>
			<p class="scribe-keyword-details-graph-description"><?php scribe_the_link_score_description( $details->scoreLinks ); ?> <?php printf( '<a data-placement="%s" rel="popover" title="%4$s" data-content="%2$s" href="%3$s">%4$s &raquo;</a>', 'left', esc_html__( 'The Link Score compares the amount of links to your site to sites that are ranking for the selected term. A Link Score above 100 indicates that you are equal to or greater than the average number of sites that are ranking for the term.', 'scribeseo' ), scribe_get_link_score_more_link( $details->scoreLinks ), esc_html__( 'Tell me more', 'scribeseo' ) ); ?></p>
		</div>
		
		<div class="scribe-keyword-details-graph-container">
			<h4><?php esc_html_e('Page Authority', 'scribeseo'); ?></h4>
			<div class="scribe-keyword-details-graph-symbol scribe-keyword-details-graph-symbol-warn"></div>
			<div class="scribe-keyword-details-graph-outer">
				<div class="scribe-keyword-details-graph">
					<div class="scribe-keyword-details-graph-bar <?php echo scribe_bar_graph_class( $details->scorePageAuthority ); ?>" style="width: <?php echo esc_attr( min( $details->scorePageAuthority, 100 ) ); ?>%;">
						<div class="scribe-keyword-details-graph-score"><?php echo ( $details->scorePageAuthority <= 100 ? (int)$details->scorePageAuthority : '100+' ); ?></div>
					</div>
				</div>
			</div>
			<p class="scribe-keyword-details-graph-description"><?php scribe_the_domain_authority_score_description( $details->scoreDomainAuthority ); ?> <?php printf( '<a data-placement="%s" rel="popover" title="%4$s" data-content="%2$s" href="%3$s">%4$s &raquo;</a>', 'right', esc_html__( 'The authority of your site, as reported by SEOMoz, is compared to the average page authority of sites that are ranking for the term. A score of 100 or greater indicates that you are on par with the average authority of sites that are ranking.', 'scribeseo' ), scribe_get_domain_authority_score_more_link( $details->scoreDomainAuthority ), esc_html__( 'Tell me more', 'scribeseo' ) ); ?></p>
		</div>
		
		<div class="scribe-keyword-details-graph-container alt">
			<h4><?php esc_html_e('Social Media Shares', 'scribeseo'); ?></h4>
			<div class="scribe-keyword-details-graph-symbol scribe-keyword-details-graph-symbol-check"></div>
			<div class="scribe-keyword-details-graph-outer">
				<div class="scribe-keyword-details-graph">
					<div class="scribe-keyword-details-graph-bar <?php echo scribe_bar_graph_class( $details->scoreFacebookLikes ); ?>" style="width: <?php echo esc_attr( min( $details->scoreFacebookLikes, 100 ) ); ?>%;">
						<div class="scribe-keyword-details-graph-score"><?php echo ( $details->scoreFacebookLikes <= 100 ? (int)$details->scoreFacebookLikes : '100+' ); ?></div>
					</div>
				</div>
			</div>
			<p class="scribe-keyword-details-graph-description"><?php scribe_the_facebook_likes_score_description( $details->scoreFacebookLikes ); ?> <?php printf( '<a data-placement="%s" rel="popover" title="%4$s" data-content="%2$s" href="%3$s">%4$s &raquo;</a>', 'left', esc_html__( 'For the this term, we compare the number of shares of your content on social media sites to the average sharing by sites that rank for the term. The higher this score, the better.', 'scribeseo' ), scribe_get_facebook_likes_score_more_link( $details->scoreFacebookLikes ), esc_html__( 'Tell me more', 'scribeseo' ) ); ?></p>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="scribe-keyword-details-analysis scribe-keyword-details-section">
		<div class="scribe-keyword-details-analysis-box-outer">
			<div class="scribe-keyword-details-analysis-box">
				<h3 class="scribe-keyword-details-analysis-box-title"><?php esc_html_e('Gender', 'scribeseo'); ?></h3>
				<ul>
					<li><strong><?php printf( '%.0f', $details->scoreGenderMale ); ?>%</strong> <?php esc_html_e( 'Male', 'scribeseo' ); ?></li>
					<li><strong><?php printf( '%.0f', $details->scoreGenderFemale ); ?>%</strong> <?php esc_html_e( 'Female', 'scribeseo' ); ?></li>
				</ul>
			</div>
		</div>
		
		<div class="scribe-keyword-details-analysis-box-outer">
			<div class="scribe-keyword-details-analysis-box">
				<h3 class="scribe-keyword-details-analysis-box-title"><?php esc_html_e('Age', 'scribeseo'); ?></h3>
				<ul>
					<li><strong><?php printf( '%.0f', $details->agePrimaryValue ); ?>%</strong> <?php echo esc_html( $details->agePrimaryDescription ); ?></li>
					<li><strong><?php printf( '%.0f', $details->ageSecondaryValue ); ?>%</strong> <?php echo esc_html( $details->ageSecondaryDescription ); ?></li>
				</ul>
			</div>
		</div>
		
		<div class="scribe-keyword-details-analysis-box-outer">
			<div class="scribe-keyword-details-analysis-box">
				<h3 class="scribe-keyword-details-analysis-box-title"><?php esc_html_e( 'Avg. $ PPC', 'scribeseo' ); ?></h3>
				<ul>
					<li><strong>$<?php printf( '%.2f', $details->ppc ); ?></strong> Bing</li>
					<li>&nbsp;</li>
				</ul>
			</div>
		</div>
		
		<div class="scribe-keyword-details-analysis-box-outer last">
			<div class="scribe-keyword-details-analysis-box last">
				<h3 class="scribe-keyword-details-analysis-box-title"><?php esc_html_e( 'Search Volume', 'scribeseo'); ?></h3>
				<ul>
					<li><strong><?php echo number_format_i18n( $details->volumeAnnual ); ?></strong> <?php esc_html_e( 'Annual', 'scribeseo' ); ?></li>
					<li><strong><?php echo number_format_i18n( $details->volumeMonthly ); ?></strong> <?php esc_html_e( 'Monthly', 'scribeseo' ); ?></li>
				</ul>
			</div>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<?php } ?>
	
</div>