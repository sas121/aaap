<table class="form-table">
	<tbody>
	<?php foreach($test_results as $test_result) { ?>
		<tr valign="top">
			<th scope="row"><?php echo esc_html($test_result['name']); ?></th>
			<td>
				<?php echo nl2br(esc_html($test_result['value'])); ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>