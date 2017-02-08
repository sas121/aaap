<?php
$keywordInfo = $info['GetAnalysisResult']['Analysis']['PrimaryKeywords'];
$analysisInfo = $info['GetAnalysisResult']['Analysis']['KeywordAnalysis'];
?>
<form method="post">
	<h3><?php esc_html_e( 'Contextual Analysis' , 'scribeseo'); ?></h3>
	<p>
		<?php echo esc_html( $keywordInfo['Description'] ); ?>
	</p>
	<table class="form-table">
		<tbody>
			<?php foreach($keywordInfo['KeywordDescriptions']['KeywordDescription'] as $keywordDescription) { ?>
				<?php if( $keywordDescription['Type'] == 'Primary Keywords') { ?>
					<th scope="row"><strong><?php echo esc_html( $keywordDescription['Type'] ); ?></strong></th>
					<td>
						<?php
						$primaryKeywords = $this->getSeoPrimaryKeywordsForPost($_GET['post']);
						if( count( $primaryKeywords ) == 0 ) {
							echo esc_html( $keywordDescription['Value'] );
						} elseif( count( $primaryKeywords ) == 1 ) {
							printf(esc_html__('The term %s is emphasized within your content and is considered a Primary Keyword.' , 'scribeseo'), '<strong>' . esc_html( $primaryKeywords[0] ) . '</strong>' );
						} elseif(count($primaryKeywords)==2) {
							printf(esc_html__('The terms %s and %s are emphasized within your content and are considered Primary Keywords.', 'scribeseo'),'<strong>' . esc_html( $primaryKeywords[0] ) . '</strong>','<strong>' . esc_html( $primaryKeywords[1] ) . '</strong>');
						} else {
							$primaryKeywords = array_map( 'esc_html', $primaryKeywords );
							$last = array_pop($primaryKeywords);
							printf(esc_html__('The terms %s, and %s are emphasized within your content and are considered Primary Keywords.', 'scribeseo'),'<strong>' . implode('</strong>, <strong>', $primaryKeywords) . '</strong>',"<strong>{$last}</strong>");
						}
						?>
					</td>
				<?php } else { ?>
				<tr>
					<th scope="row"><strong><?php echo esc_html( $keywordDescription['Type'] ); ?></strong></th>
					<td>
						<?php echo wp_kses( $keywordDescription['Value'], Scribe_SEO::formatting_allowedtags() ); ?>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>
	<h3><?php esc_html_e( 'Keyword Analysis' , 'scribeseo'); ?></h3>
	<p>
		<?php echo esc_html( $analysisInfo['Description'] ); ?>
	</p>
	<table class="widefat" style="width: 99%;">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Keywords' , 'scribeseo'); ?></th>
				<th scope="col"><?php esc_html_e( 'Rank' , 'scribeseo'); ?></th>
				<th scope="col"><?php esc_html_e( 'Prominence' , 'scribeseo'); ?></th>
				<th scope="col"><?php esc_html_e( 'Frequency' , 'scribeseo'); ?></th>
				<th scope="col"><?php esc_html_e( 'Density' , 'scribeseo'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if( !is_array($keywordInfo['Keywords']['Keyword'] ) ) { ?>
				<tr class="normal">
					<td colspan="6" style="text-align: center;"><?php esc_html_e( 'No keywords found.' , 'scribeseo'); ?></td>
				</tr>
			<?php } else { 
				foreach( $keywordInfo['Keywords']['Keyword'] as $keyword) { ?>
			<tr class="normal">
				<th scope="row"><?php echo esc_html( $keyword['Term'] ); ?></td>
				<td><?php echo esc_html( $keyword['Rank'] ); ?></td>
				<td><?php echo esc_html( $keyword['Prominence'] ); ?></td>
				<td><?php echo esc_html( $keyword['Frequency'] ); ?></td>
				<td><?php printf( '%.2f%%', $keyword['Density'] ); ?></td>
			</tr>
			<?php } } ?>
		</tbody>
	</table>
</form>

