<?php

class Synthesis_Resource_Monitor {
	const VERSION = 1;
	const RESOURCE_QUOTA_FILE = '.synthquota';
	const BANDWIDTH_OVERAGE_COST = 0.15;

	public static function start() {
		// Register notices for quota notification.
		add_action( 'admin_notices', array( __CLASS__, 'disk_quota_notification' ) );
		add_action( 'admin_init', array( __CLASS__, 'bandwidth_quota_notification' ) );
	}

	/**
	 * Displays the disk usage bar.
	 */
	public static function disk_quota_markup() {
		$quota_info = self::get_resource_usage();
		if ($quota_info != null) {
			$used = $quota_info->disk_used;
			$quota = $quota_info->soft_quota;
			$percent = intval(($used / $quota) * 100);

			// Select the color of the bar.
			$color = 'green';
			if ($percent >= 80) {
				$color = 'yellow';
			}
			if ($percent >= 100) {
				$color = 'red';
			}
			?>
			<h3 class="title"><?php _e('Disk Quota'); ?></h3>
			<div id="synthesis-disk-quota">
				<p>
					<?php _e('Disk quota usage:'); ?>
					<span class="quota-text"><?php echo esc_html($percent); ?>%<span>
				</p>

				<div class="quota-progress-wrapper">
					<div class="quota-progress <?php echo esc_attr($color); ?>"
						 style="width:<?php echo $percent; ?>%;max-width: 100%;"></div>
				</div>
			</div>
		<?php
		}
	}

	/**
	 * Displays the bandwidth usage bar.
	 */
	public static function bandwidth_quota_markup() {
		$quota_info = self::get_resource_usage();
		if ($quota_info != null) {
			$used = $quota_info->bandwidth_used;
			$quota = $quota_info->bw_quota;
			$percent = intval(($used / $quota) * 100);

			// Select the color of the bar.
			$color = 'green';
			if ($percent >= 80) {
				$color = 'yellow';
			}
			if ($percent >= 100) {
				$color = 'red';
			}
			?>
			<h3 class="title"><?php _e('Bandwidth Quota'); ?></h3>
			<div id="synthesis-disk-quota">
				<p>
					<?php _e('Bandwidth quota usage:'); ?> <span class="quota-text"><?php echo esc_html($percent); ?>%<span>
				</p>

				<div class="quota-progress-wrapper">
					<div class="quota-progress <?php echo esc_attr($color); ?>"
						 style="width:<?php echo $percent; ?>%;max-width: 100%;"></div>
				</div>
			</div>
		<?php
		}
	}

	/**
	 * (Callback) If it looks like the user might exceed their allotted bandwidth.
	 * 		1. Add a warning notification to the header.
	 */
	public static function bandwidth_quota_notification() {
		if ( !get_option( 'synthesis_bandwidth_off' ) && date( 'j' ) != '1' && date( 'j' ) != date( 't' ) ) {
			if ( !preg_match( "/^(ws(e|x|a)?|ap|wphostco)[0-9]{1,5}\.(wsynth|wphost)\.(net|co)$/", gethostname() ) ) {
				$bandwidth_usage = self::get_bandwidth_usage_data( self::get_resource_usage() );

				// Check to see if there's a risk of going over bandwidth quota.
				if ( self::should_warn_about_bandwidth( $bandwidth_usage ) ) {
					// Add the header notification
					add_action( 'admin_notices', array( __CLASS__, 'bandwidth_quota_admin_notification' ) );
				}
			}
		}
	}

	/**
	 * Display the bandwidth usage warning, with a wrapper for displaying as an admin error.
	 */
	public static function bandwidth_quota_admin_notification() {
		$bandwidth_usage = self::get_bandwidth_usage_data( self::get_resource_usage() );
		echo '<div class="error"><p>';
		include( "views/bandwidth-warning-message.php" );
		echo '</p></div>';
	}

	/**
	 * Display the disk usage warning as an admin error.
	 */
	public static function disk_quota_notification() {
		// Only show the warning to administrators.
		if (current_user_can('install_plugins')) {
			$disk_usage = self::get_resource_usage();
			if (null != $disk_usage) {
				$used = $disk_usage->disk_used;
				$quota = $disk_usage->soft_quota;
				if (null != $quota) {
					$percent = intval(($used / $quota) * 100);
					if ($percent >= 100) {
						?>
						<div class="error">
						<p>
							<?php
							printf(__('You have reached <strong>%d%% (%dMb)</strong> of your disk usage limit of <strong>%dMb</strong>.<br />' .
								'You may lose the ability to upload files if you continue increasing your disk usage.'), $percent, $used, $quota);
							?>
						</p>
						</div><?php
					}
				}
			}
		}
	}

	/**
	 * Get the resource usage and quota data.
	 * @return array|mixed|null The decoded resource file if it exists and is deserializable.
	 */
	public static function get_resource_usage() {
		$quota_path = ABSPATH . self::RESOURCE_QUOTA_FILE;

		if (file_exists($quota_path)) {
			$quota_file = file_get_contents($quota_path);
			return json_decode($quota_file);
		}
		return null;
	}

	/**
	 * Create the bandwidth usage array for bandwidth warning views.
	 * @param $resource_usage Resource data, created by get_resource_usage.
	 * @return array Array containing the data for bandwidth warning view.
	 */
	public static function get_bandwidth_usage_data( $resource_usage ) {

		$bandwidth_usage = array();

		$bandwidth_usage['day_of_month'] = date('j');
		$bandwidth_usage['days_in_current_month'] = date('t');

		$bandwidth_usage['used'] = (isset($resource_usage->bandwidth_used)) ? $resource_usage->bandwidth_used : null;
		$bandwidth_usage['quota'] = (isset($resource_usage->bw_quota)) ? $resource_usage->bw_quota : null;

		if (null != $bandwidth_usage['quota']) {
			$bandwidth_usage['projected_safe_usage'] = ($bandwidth_usage['quota'] / $bandwidth_usage['days_in_current_month']) * $bandwidth_usage['day_of_month'];
		}

		// Usage scaling (MB to GB etc.)
		// Currently no scaling is needed so value is 1
		$bandwidth_usage['scaling'] = 1;
		$bandwidth_usage['usage'] = $bandwidth_usage['used'] / $bandwidth_usage['scaling'];
		$bandwidth_usage['allotment'] = $bandwidth_usage['quota'] / $bandwidth_usage['scaling'];

		// Compute the usage as the amount used per day so far multiplied by the number of days.
		$bandwidth_usage['projected_usage'] = ($bandwidth_usage['usage'] / $bandwidth_usage['day_of_month']) * $bandwidth_usage['days_in_current_month'];

		// Compute the projected differences, %, and cost.
		$bandwidth_usage['projected_difference'] = $bandwidth_usage['projected_usage'] - $bandwidth_usage['allotment'];
		$bandwidth_usage['projected_difference_percent'] = $bandwidth_usage['allotment'] ? ($bandwidth_usage['projected_difference'] / $bandwidth_usage['allotment']) * 100 : 0;
		$bandwidth_usage['projected_cost'] = self::BANDWIDTH_OVERAGE_COST * $bandwidth_usage['projected_difference'];

		return $bandwidth_usage;
	}

	/**
	 * Determine if this user can see bandwidth warnings.
	 * Will return for the super admin on multisite, or an admin on a normal install.
	 *
	 * @param $user_id int The id of the user to check permissions for
	 * @return bool If the user should be shown the warning.
	 */
	public static function user_can_see_warnings( $user_id ) {
		return ( function_exists('is_multisite') && is_multisite() && user_can( $user_id, 'manage_network' ) )
			|| ( ( !function_exists('is_multisite') || !is_multisite() ) && user_can( $user_id, 'install_plugins' ) );
	}

	/**
	 * Determine if bandwidth usage shows potential for overage.
	 * @param array $bandwidth_usage Bandwidth usage data.
	 * @return bool
	 */
	public static function should_warn_about_bandwidth( $bandwidth_usage ) {
		if ( self::user_can_see_warnings( get_current_user_id() ) ) {
			if (isset($bandwidth_usage['projected_safe_usage'])) {
				return ($bandwidth_usage['used'] >= $bandwidth_usage['projected_safe_usage']);
			}
		}
		return false;
	}
}

Synthesis_Resource_Monitor::start();
