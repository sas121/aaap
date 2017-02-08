<?php
$scoreInfo = $info['GetAnalysisResult']['Analysis']['SeoScore'];
$score = $scoreInfo['Score']['Value'];
$description = $scoreInfo['Score']['Description'];
$sections = $scoreInfo[ 'Sections' ][ 'SeoScoreSection' ];
$sectionCount = count( $sections );
$firstSection = array_shift($sections);
?>
<form method="post">
	<p>

		<?php
		echo esc_html( $scoreInfo['Description'] );
		?>
	</p>
	<table class="widefat" style="width:99%" id="ecordia-analysis-score-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Overall' , 'scribeseo'); ?></th>
				<th scope="col"><?php esc_html_e( 'Content' , 'scribeseo'); ?></th>
				<th scope="col"><?php esc_html_e( 'Analysis &amp; Recommendations' , 'scribeseo'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id="ecordia-score-analysis-overview" class="ecordia-middle-cell <?php echo sanitize_html_class( $this->getSeoScoreClassForPost( $scoreInfo['Score']['Value'] ) ); ?>-background" rowspan="<?php echo esc_attr( $sectionCount ); ?>">
					<div id="overall-score-analysis"><?php printf( esc_html__( '%d%%' , 'scribeseo'), $scoreInfo['Score']['Value']); ?></div>
					<p><?php echo esc_html( $scoreInfo['Score']['Description'] ); ?></p>
				</td>
				<?php $this->displaySection($firstSection); ?>
			</tr>
			<?php foreach( $sections as $section ) { ?>
			<tr>
				<?php $this->displaySection( $section ); ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</form>

