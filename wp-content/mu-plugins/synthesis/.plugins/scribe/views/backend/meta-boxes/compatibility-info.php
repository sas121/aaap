<?php $user = wp_get_current_user(); ?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="scribe-compatibility-name"><?php _e('Your Name', 'scribeseo'); ?></label></th>
			<td>
				<input class="regular-text" type="text" name="scribe-compatibility[name]" id="scribe-compatibility-name" value="<?php esc_attr( $user->display_name ); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="scribe-compatibility-email"><?php _e('Your Email', 'scribeseo'); ?></label></th>
			<td>
				<input class="regular-text" type="text" name="scribe-compatibility[email]" id="scribe-compatibility-email" value="<?php esc_attr( get_option( 'admin_email' ) ); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="scribe-compatibility-issue"><?php _e('Your Issue or Comments', 'scribeseo'); ?></label></th>
			<td>
				<textarea class="large-text" rows="6" name="scribe-compatibility[issue]" id="scribe-compatibility-issue"></textarea><br />
				<?php _e( 'Clicking the <strong>Send Report</strong> button will forward the above form, along with the details about your installation below to the Scribe SEO support team.', 'scribeseo' ); ?>
				<input type="hidden" id="scribe-compatibility-screen-size" name="scribe-compatibility[screen-size]" />
				<input type="hidden" id="scribe-compatibility-window-size" name="scribe-compatibility[window-size]" />
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#scribe-compatibility-screen-size').val(screen.width + 'x' + screen.height + 'px');
	$('#scribe-compatibility-window-size').val($(window).width() + 'x' + $(window).height() + 'px');
});
</script>
			</td>
		</tr>
	</tbody>
</table>