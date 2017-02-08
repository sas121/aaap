<?php
if ($keywordAlternates != null) {
	$denom = $keywordAlternates[0]['AnnualSearchVolume'];
} else {
	$no_data = true;
	$no_data_text = __('No alternate keywords found', 'scribeseo');
	$denom = 1;
}
?>
<table class="widefat alternate-keywords-table">
	<thead>
		<tr>
			<th scope="col"><?php esc_html_e('Alternate Keyword Suggestions', 'scribeseo'); ?></th>
			<th scope="col"><?php esc_html_e('Relative Search Frequency', 'scribeseo'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col"><?php esc_html_e('Alternate Keyword Suggestions', 'scribeseo'); ?></th>
			<th scope="col"><?php esc_html_e('Relative Search Frequency', 'scribeseo'); ?></th>
		</tr>
	</tfoot>
	<tbody>
	<?php foreach((array)$keywordAlternates as $alternate) { ?>
		<?php
		$width = ceil($alternate['AnnualSearchVolume'] / $denom * 100);
		?>
		<tr>
			<?php if($no_data) { ?>
			<td colspan="2" style="text-align: center;"><?php echo esc_html( $no_data_text ); ?></td>
			<?php } else { ?>
			<td><?php echo esc_html( $alternate['Term'] ); ?></td>
			<td class="search-volume">
				<div class="scribe-alternate-keyword-relative-search-volume" style="width: <?php printf( '%d', $width ); ?>%;"><?php echo esc_html( $alternate['AnnualSearchVolume'] ); ?></div>
			</td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>