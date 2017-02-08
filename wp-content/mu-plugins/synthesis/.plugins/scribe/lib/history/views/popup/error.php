<?php
$message = urldecode( $_GET[ 'message' ] );
$extended = urldecode( $_GET[ 'extended' ] );
$settings = urldecode( $_GET[ 'show-settings'] );
?>
<form method="post" id="ecordia-error-message">
	<p class="ecordia-error ecordia-center" style="margin-bottom: 2px;"><?php echo esc_html($message); ?></p>
	<p class="ecordia-center" style="margin-top: 2px;">
		<?php
		if( 'show-settings-prompt' == $extended ) {
			_e( 'Upgrade your Ecordia account under Settings.' , 'scribeseo');
			?>
			<br /><a id="ecordia-setttings-page-from-thickbox" target="_blank" href="<?php echo esc_url( admin_url( 'options-general.php?page=ecordia' ) ); ?>"><?php _e( 'Go to Settings' , 'scribeseo'); ?></a>&nbsp;&nbsp;<a href="#" class="ecordia-close-thickbox"><?php _e( 'Close' , 'scribeseo'); ?></a>
			<?php
		} else {
			echo esc_html( $extended );
			?>
			<br /><a href="#" class="ecordia-close-thickbox"><?php _e( 'Close' , 'scribeseo'); ?></a>
			<?php
		}
		?>
	</p>
</form>
<script type="text/javascript">
	jQuery('html').css('overflow','hidden');
</script>
