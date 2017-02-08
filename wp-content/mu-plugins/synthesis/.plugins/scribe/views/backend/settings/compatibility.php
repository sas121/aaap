<div class="wrap scribe-metaboxes">
	<form method="post" action="<?php esc_url(add_query_arg(array())); ?>">
		<?php wp_nonce_field('send-scribe-compatibility-report', 'send-scribe-compatibility-report-nonce'); ?>
		
		<?php screen_icon(); ?>
		<h2>
			<?php esc_html_e('Scribe v4 Compatibility Report', 'scribeseo'); ?>
			<input type="submit" class="button button-primary" id="send-scribe-compatibility-report" name="send-scribe-compatibility-report" value="<?php esc_attr_e('Send Report', 'scribeseo'); ?>" />
		</h2>

		<div class="metabox-holder">
			<?php settings_errors(); ?>
				
			<div id="main-sortables" class="meta-box-sortables">
				<?php do_meta_boxes('scribe-compatibility', 'normal', null); ?>
			</div>
		</div>
		
		<div class="bottom-buttons">
			<input type="submit" class="button button-primary" id=="send-scribe-compatibility-report" name="send-scribe-compatibility-report" value="<?php esc_attr_e('Send Report', 'scribeseo'); ?>" />
		</div>
		
	</form>
</div>