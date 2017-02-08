<?php

/**
 * Plugin Name: Synthesis System Message
 * Version: 1.1.0
 * Description: Shows an admin notice when there is a Synthesis status message available in the customer portal.
 * Plugin Author: CopyBlogger Media
 * Plugin URL: http://websynthesis.com
 */

class Synthesis_System_Message {

    const VERSION = '1.1.0';
    const CONF_FILE = 'sysmsg/sysmsg.conf';
    static $conf_file_path;

	public static function start() {
		self::$conf_file_path = SYNTHESIS_CHILD_PLUGIN_DIR . self::CONF_FILE;
		if ( file_exists( self::$conf_file_path ) ) {
			$contents = file_get_contents( self::$conf_file_path );
			list($value, $flag) = explode( '=', $contents );
			if ( intval( $flag ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'show_system_message' ) );
				add_action( 'wp_ajax_synthesis_system_message_dismiss', array( __CLASS__, 'ajax_dismiss' ) );
			}
		}
	}

	public static function show_system_message() {
		if ( self::should_show_message() ) {
			$date = filemtime( self::$conf_file_path );
			?>
			<div class="updated" id="synthesis-system-message-notice">
				<p>
					<?php
					printf( __( '<strong>%s</strong>: There is an important message in the Synthesis Customer Portal.' ), date( 'F j, Y', $date ) );
					?>
					<a href="https://accounts.websynthesis.com/" target="_blank"><?php _e( 'View Message' ); ?></a>
					<input type="button" id="synthesis-system-message-dismiss" class="button" value="<?php _e( 'Dismiss' ); ?>" />
				</p>
			</div>
			<script type="text/javascript">
				jQuery(function($){
					$('#synthesis-system-message-dismiss').click(function(){
						$.post(ajaxurl, {
							'action' : 'synthesis_system_message_dismiss'
						}, function() {
							$('#synthesis-system-message-notice').hide();
						});
					});
				});
			</script>
		<?php
		}
	}

	public static function ajax_dismiss() {
		if ( self::should_show_message() ) {
			chmod( self::$conf_file_path, 0600 );
			$handle = fopen( self::$conf_file_path, 'w' );
			fwrite( $handle, 'SYSMSG=0' );
			fclose( $handle );
			chmod( self::$conf_file_path, 0400 );
		}
	}

	public static function should_show_message() {
		return ( current_user_can( 'manage_options' ) && !is_multisite() ) || current_user_can( 'manage_network' );
	}
}

Synthesis_System_Message::start();
