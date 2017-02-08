<div class="scribe-wrap">
	
	<div class="scribe-keyword-analysis-metrics">
		<h3><?php esc_html_e('Keyword Analysis Metric', 'scribeseo'); ?></h3>
		<div class="scribe-document-analysis-keyword-analysis-metric-outer scribe-popup-analysis-score-wrap">
			<div class="scribe-wrap scribe-content-analysis-score scribe-analysis-meta-box-score" style="float: left; width: 30%">
				<?php esc_html_e( 'Site Score: ', 'scribeseo' ); ?>
				<span class="<?php echo scribe_score_class( $content_analysis->scribeScore, 'site' ); ?>">
				<?php
				if ( empty( $content_analysis ) ) {
					 esc_html_e( '-', 'scribeseo' );
				} elseif ( isset( $content_analysis->scribeScore ) ) {
					printf( '%d', $content_analysis->scribeScore );
				} else {
					esc_html_e( 'N/A', 'scribeseo' );
				}
				?>
				</span>
			</div>
			<span class="scribe-wrap" style="float: left; width: 55%"><?php echo wp_kses( scribe_get_content_analyis_site_score_text( $content_analysis->scribeScore, $content_analysis->keywords ), Scribe_SEO::formatting_allowedtags() ); ?></span>
			<div class="clear"></div>
		</div>
		<p><?php printf( esc_html__('Keyword Analysis inspects your page to compare the use of keywords against two metric - the usage of the keyword on your site (Search Metric) and recommended copywriting styles (Copy Styles). The stronger these two metrics, the better your score. To improve the score for a keyword, go to %1$s for more details.', 'scribeseo'), '<a href="' . add_query_arg( array( 'type' => 'scribe-analysis-keywords', 'tab' => 'scribe-analysis-document' ) ) . '">' . esc_html__( 'Content Optimizer', 'scribeseo' ) . '</a>' ); ?></p>

		<div>
			<a class="scribe-tip-marker scribe-show-tip" href="#">+</a>
			<a class="scribe-tip-marker scribe-hide-tip scribe-link-building-tip" href="#">-</a>
			<div class="scribe-link-building-tip">
<p>The four quadrants (A, B, C, and D) show a comparison of how well the keywords Scribe found in your content will perform, based on two metrics:</p>

<ul>
    <li>1. How well is the content optimized for the keyword (like the old Scribe did)?</li>
    <li>2. How well is the site where the content will be published optimized for the keyword?</li>
</ul>

<p>Here's a few details regarding what each quadrant means:</p>

<p><strong>A -</strong> The page and site are superbly optimized for the keyword used; this is the holy grail. <em>Example</em>: Copyblogger.com publishing an article that is well optimized for the term "copywriting."</p>

<p><strong>B -</strong> The page is not well optimized for the keyword, but the site is. Optimize the page for the keyword and the keyword should move into the A quadrant. <em>Example</em>: Copyblogger.com publishing an article that is not well optimized for the term "copywriting."</p>

<p><strong>C -</strong> The page is optimized for the keyword, but the site is not. This may be a new site or a new keyword that you're targeting. It will take some time to get more optimized content published for this keyword to eventually move the keyword to quadrant A. <em>Example</em>: Copyblogger.com publishing an article that is optimized for the keyword "pizza."</p>

<p><strong>D -</strong> Neither the site nor the page are optimized for the keyword. This may be a new site. Start by optimizing the page for the keyword in order to move it into the C quadrant and then publish more optimized content over time to eventually move it to the A quadrant. <em>Example</em>: Copyblogger.com publishing an article that is not well optimized for the keyword "pizza."</p>
			</div>
			<div class="clear"></div>
		</div>
		<div id="scribe-keyword-analysis-graph-container-container">
			<div id="scribe-keyword-analysis-graph-container">
				
			</div>
		</div>
	</div>
	
	<div class="scribe-keyword-analysis-details">
		<h3><?php esc_html_e( 'Keyword Details', 'scribeseo' ); ?></h3>
		<p><?php esc_html_e( 'A comprehensive assessment of every keyword found within the content.', 'scribeseo' ); ?></p>
	
		<table class="widefat scribe-keyword-analysis-details-table">
			<thead>
				<tr valign="top">
					<th scope="col" class="scribe-keyword-analysis-details-table-keyword"><?php esc_html_e('Keyword', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-score"><?php esc_html_e('Grade', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-rank"><?php esc_html_e('Rank', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-prominence"><?php esc_html_e('Prominence', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-frequency"><?php esc_html_e('Frequency', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-density"><?php esc_html_e('Density', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-volume"><?php esc_html_e('Monthly Search Volume', 'scribeseo'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr valign="top">
					<th scope="col" class="scribe-keyword-analysis-details-table-keyword"><?php esc_html_e('Keyword', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-score"><?php esc_html_e('Grade', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-rank"><?php esc_html_e('Rank', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-prominence"><?php esc_html_e('Prominence', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-frequency"><?php esc_html_e('Frequency', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-density"><?php esc_html_e('Density', 'scribeseo'); ?></th>
					<th scope="col" class="scribe-keyword-analysis-details-table-volume"><?php esc_html_e('Monthly Search Volume', 'scribeseo'); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$has_kwsv = false;
				foreach( $content_analysis->keywords as $keyword ) {
					$has_kwsv |= isset( $keyword->kwsv );
				?>
				<tr data-kwod="<?php echo esc_attr( $keyword->kwod ); ?>" data-x="<?php printf( '%.2f', max( min( $keyword->kwc, 4.85 ), 0.15 ) ); ?>" data-y="<?php printf( '%.2f', scribe_get_keyword_analysis_data_y( $keyword->kws ) ); ?>" data-pad="<?php printf( '%d', $keyword->padding ); ?>" valign="top" class="alternate">
					<td class="scribe-keyword-analysis-details-table-keyword">
						<a href="<?php echo esc_url( $document_tab_url ); ?>" title="<?php echo esc_attr( scribe_get_keyword_analysis_score_text( $keyword->kws ) ); ?>"><?php echo esc_html( ucwords( $keyword->text ) ); ?></a>
					</td>
					<td class="scribe-keyword-analysis-details-table-score"><?php echo esc_attr( $keyword->kwo ); ?></td>
					<td class="scribe-keyword-analysis-details-table-rank"><?php echo esc_html( $keyword->kwlText ); ?></td>
					<td class="scribe-keyword-analysis-details-table-prominence"><?php echo esc_html( $keyword->kwp ); ?></td>
					<td class="scribe-keyword-analysis-details-table-frequency"><?php echo esc_html( $keyword->kwf ); ?></td>
					<td class="scribe-keyword-analysis-details-table-density"><?php printf( esc_html__( '%.2f%%', 'scribeseo' ), $keyword->kwd ); ?></td>
					<td class="scribe-keyword-analysis-details-table-volume"><?php if ( $has_kwsv ) { echo number_format( $keyword->kwsv ); } ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
// In Keyword Analysis Pop Up, plot the chart

jQuery(document).ready(function($) {
	setTimeout('scribe_keyword_analysis_plot()', 250);

<?php
	// hide keyword search volume on analysis that doesn't have it
	if ( ! $has_kwsv ) {
?>
	$('.scribe-keyword-analysis-details-table-volume').hide();
<?php
	}
?>	
});
</script>