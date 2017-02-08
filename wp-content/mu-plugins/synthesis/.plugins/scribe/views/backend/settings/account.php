<div class="wrap scribe-metaboxes">
	<form method="post" action="<?php esc_url(add_query_arg(array())); ?>">
		<?php screen_icon(); ?>
		<h2>
			<?php 
			_e('Scribe - Account Information', 'scribeseo');
			$upgrade_url = sprintf( 'mailto:support@copyblogger.com?subject=Upgrade+Request&body=This+is+a+request+to+upgrade+the+account+for+%s.+Please+contact+me+for+upgrade+options.', $settings['api-key'] );
			?>
			<a class="button button-secondary" href="<?php echo esc_url( $upgrade_url ); ?>"><?php _e('Upgrade Account', 'scribeseo'); ?></a>
		</h2>

		<div class="metabox-holder">
			<?php settings_errors(); ?>
				
			<div id="main-sortables" class="meta-box-sortables">
				<?php if(is_wp_error($account)) { ?>
	
				<div class="error" id="scribe-account-error"><p><?php printf(__('Your account could not be retrieved. Please ensure that you have set your API key correctly on the <a href="%1$s">settings</a> page.', 'scribeseo'), add_query_arg(array('page' => 'scribe-settings'), admin_url('options-general.php'))); ?></p></div>
				
				<?php } else { ?>
				
				<?php do_meta_boxes('scribe-account', 'normal', $account); ?>
				
				<?php } ?>
			</div>
		</div>
		
		<div class="bottom-buttons">
			<a class="button button-secondary" href="<?php echo esc_url( $upgrade_url ); ?>"><?php _e( 'Upgrade Account' , 'scribeseo'); ?></a>
		</div>
		
	</form>
</div>