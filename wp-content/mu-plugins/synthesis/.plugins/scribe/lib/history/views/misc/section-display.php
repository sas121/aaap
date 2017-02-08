	<td class="ecordia-middle-cell" >
		<?php echo esc_html( $section['Name'] ); ?>
	</td>
	<td class="ecordia-no-pad">
		<ul>
			<?php foreach( $section['Items']['SeoScoreSectionItem'] as $sectionItem ) { ?>
			<li id="<?php echo esc_attr( $sectionItem['Id'] ); ?>" class="<?php echo ($sectionItem['IsPassing'] == 'true') ? 'complete' : 'warn'; ?>">
				<?php echo wp_kses( $sectionItem[ 'Text' ], Scribe_SEO::formatting_allowedtags() ); ?>
			</li>
			<?php } ?>
		</ul>
	</td>
