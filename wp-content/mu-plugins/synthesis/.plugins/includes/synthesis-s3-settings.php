<?php

class Synthesis_S3_Settings {

	const FOLDER_DELIMITER = "\n";

	const PUSH_FOLDER_LIMIT = 10;
	const RSYNC_FOLDER_LIMIT = 3;

	const STATUS_FILE = ".s3backupstatus";

	const BACKUP_GUIDE_URL = 'http://ssensor.s3.amazonaws.com/personal_backups_guide.pdf';

	public static function get_s3_backup_setting_defaults() {
		$defaults = array(
			'aws-access-key'      => '',
			'aws-secret-key'      => '',
			's3-bucket'           => '',
			's3-rsync-bucket'     => '',
			's3-copies'           => 10,
			's3-backup-databases' => '',
			's3-push-folders'     => self::get_default_push_folders(),
			's3-rsync-folders'    => '',
		);
		return $defaults;
	}

	public static function get_s3_backup_settings() {
		$defaults = self::get_s3_backup_setting_defaults();

		$settings = array();
		foreach ( $defaults as $key => $default_value ) {
			$settings[$key] = get_option( 'synthesis_s3_' . $key, $default_value );
		}

		return $settings;
	}

	public static function save_s3_backup_settings( $settings ) {
		$defaults = self::get_s3_backup_setting_defaults();
		$settings = wp_parse_args( $settings, $defaults );

		$errors = array();

		foreach ( $settings as $key => $value ) {
			$settings[$key] = self::sanitize_setting( $key, $value, $errors );
		}

		if ( !empty( $settings['s3-bucket'] ) ) {
			self::test_s3_connection( $settings['aws-access-key'], $settings['aws-secret-key'], $settings['s3-bucket'], $errors );
		}

		if ( !empty( $settings['s3-rsync-bucket' ] ) ) {
			self::test_s3_connection( $settings['aws-access-key'], $settings['aws-secret-key'], $settings['s3-rsync-bucket'], $errors );
		}

		foreach ( $settings as $key => $value ) {
			update_option( 'synthesis_s3_' . $key, $value );
		}

		return $errors;
	}

	public static function sanitize_setting( $name, $value, &$errors ) {
		switch ( $name ) {
			case 's3-push-folders':
				return self::sanitize_folder_paths( $value, self::PUSH_FOLDER_LIMIT, $errors );
			case 's3-rsync-folders':
				return self::sanitize_folder_paths( $value, self::RSYNC_FOLDER_LIMIT, $errors );
			case 's3-backup-databases':
				$db_rows = preg_split( '/[\r\n]+/', $value );
				foreach ( $db_rows as $db_info ) {
					self::test_mysql_connection( $db_info, $errors );
				}
				return $value;
			default:
				return $value;
		}
	}

	public static function test_mysql_connection( $connection_string, &$errors ) {
		if ( !empty( $connection_string ) ) {
			$info_parts = explode( ':', $connection_string );
			$db_name = $info_parts[0];
			$db_user = $info_parts[1];
			$db_pass = $info_parts[2];

			$test_connection = @mysql_connect( 'localhost', $db_user, $db_pass );
			if ( !$test_connection ) {
				$errors[] = sprintf( __( 'Couldn\'t connect to \'%s\'. Double check your credentials' ), $connection_string );
			} else if ( !@mysql_select_db( $db_name, $test_connection ) ) {
				$errors[] = sprintf( __( 'Couldn\'t select database for \'%s\'. Double check the database name' ), $connection_string );
			}
		}
	}

	public static function test_s3_connection( $access_key, $secret_key, $bucket, &$errors, $host = '' ) {
		$bucket_parts = explode( '/', $bucket );
		if ( empty( $host ) ) {
			$host = "http://{$bucket_parts[0]}.s3.amazonaws.com";
		}
		$date = gmdate('D, d M Y H:i:s T');
		$headers = array(
			'Date' => $date
		);
		$signature_content =
			"HEAD\n" .
			"\n" . // MD5
			"\n" . // Content type
			"{$date}\n" .
			"/{$bucket_parts[0]}/";
		$headers['Authorization'] = "AWS $access_key:" . base64_encode( hash_hmac( 'sha1', $signature_content, $secret_key, true ));
		$response = wp_remote_head( $host, array(
			'headers' => $headers
		) );
		$code = intval($response['response']['code']);
		$message = $response['response']['message'];
		if ( 404 == $code ) {
			$errors[] = sprintf( __( 'Bucket "%s" came back with code %d: %s. Check that this bucket exists' ), $bucket, $code, $message );
			return;
		}
		if ( 307 == $code ) {
			$location = $response['headers']['location'];
			self::test_s3_connection( $access_key, $secret_key, $bucket, $errors, $location );
			return;
		} else if ( 200 !== $code ) {
			$errors[] = sprintf( __( 'Bucket "%s" came back with code %d: %s. Check your credentials and bucket permissions' ), $bucket, $code, $message );
		}
	}

	public static function sanitize_folder_paths( $paths_string, $limit, &$errors ) {
		$folders = explode( self::FOLDER_DELIMITER, $paths_string );
		if ( count( $folders ) > $limit ) {
			$errors[] = sprintf( __( 'Only %d push folders allowed. Extra folders have been removed' ), $limit );
			while ( count( $folders ) > $limit ) {
				array_pop( $folders );
			}
		}
		$filtered_folders = array();
		foreach ( $folders as $folder ) {
			$filtered = self::sanitize_folder_path( $folder, $errors );
			if ( !empty( $filtered ) ) {
				$filtered_folders[] = $filtered;
			}
		}
		return implode( self::FOLDER_DELIMITER, $filtered_folders );
	}

	public static function sanitize_folder_path( $path, &$errors ) {
		if ( empty( $path ) ) {
			return '';
		}

		// Replace '..' and make Unix/Windows paths uniform
		$path = str_replace( '\\', '/', $path );
		$abs_path = str_replace( '\\', '/', ABSPATH );

		$new_path = $path;
		do {
			$path = $new_path;
			$new_path = str_replace( '..', '', $new_path );
			$new_path = str_replace( '//', '/', $new_path );
			$new_path = str_replace( '/./', '/', $new_path );
		} while ( $new_path != $path );

		// Make sure the directory is inside this site
		if ( 0 !== strpos( $path, $abs_path ) ) {
			// Remove the folder if they try to include a directory outside this site
			if ( 0 === strpos( $path, dirname( $abs_path ) ) ) {
				$error[] = __( 'You cannot include folders outside your site' );
				return '';
			}
			if ( $path[0] == '/' ) {
				$path = untrailingslashit( $abs_path ) . $path;
			} else {
				$path = trailingslashit( $abs_path ) . $path;
			}
		}

		// Check whether the folder exists
		if ( !file_exists( $path ) ) {
			$errors[] = __( "The path $path does not exist" );
		}

		return $path;
	}

	public static function get_default_push_folders() {
		return ABSPATH . "wp-content/themes" . self::FOLDER_DELIMITER .
			ABSPATH . 'wp-content/uploads/' . date( 'Y', current_time( 'timestamp' ) ) . self::FOLDER_DELIMITER .
			ABSPATH . 'wp-content/plugins';
	}

	private static function get_s3_status_markup() {
		$status_path = ABSPATH . self::STATUS_FILE;
		if ( !file_exists( $status_path ) ) {
			return __( "No backups have been performed" );
		}

		$status_file = file_get_contents( $status_path );
		$status = json_decode( $status_file );
		if ( $status == null ) {
			// Unable to parse status file
			return __( "Unable to determine the last backup status" );
		}
		$timestamp = date( "F jS, Y, \\a\\t g:ia", strtotime( $status->time ) );

		if ( $status->status == "false" ) {
			// Backup did not succeed. Display message(s).
			$error_message =
				'<div class="s3-error"><p>'
					. sprintf(__( 'The last backup attempt failed at %s.' ), $timestamp ) . '</p>';
			$error_message .= '<p>' . __( 'Log Messages:' ) . '</p>';
			foreach ( $status->msg as $message ) {
				$error_message .= "<p class='backup-status-message'>$message</p>";
			}
			$error_message .= '</div>';
			return $error_message;
		} else {
			return sprintf( __( "The last backup was successful and completed at %s" ), $timestamp );
		}
	}

	public static function s3_settings_markup( $errors = array() ) {
		$s3_backup = self::get_s3_backup_settings();
		$s3_status = self::get_s3_status_markup();

		?>
		<div class="backup-status">
			<?php echo $s3_status; ?>
		</div>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="aws-access-key">AWS Access Key</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="aws-access-key" id="aws-access-key" value="<?php echo esc_attr( $s3_backup['aws-access-key'] ); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="aws-secret-key">AWS Secret Key</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="aws-secret-key" id="aws-secret-key" value="<?php echo esc_attr( $s3_backup['aws-secret-key'] ); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="s3-bucket">S3 Bucket</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="s3-bucket" id="s3-bucket" value="<?php echo esc_attr( $s3_backup['s3-bucket'] ); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="s3-copies"><?php _e( 'Backups To Keep' ); ?></label></th>
				<td>
					<input type="text" name="s3-copies" id="s3-copies" value="<?php echo esc_attr( $s3_backup['s3-copies'] ); ?>"/>
				</td>
			</tr>
			<tr>
			<th scope="row"><label for="s3-backup-databases"><?php _e( 'Additional Databases' ); ?></label></th>
				<td>
					<textarea class="regular-text" name="s3-backup-databases" id="s3-backup-databases" rows="3" cols="30"><?php echo esc_textarea( $s3_backup['s3-backup-databases'] ); ?></textarea>
					<br/>
					<span class="description"><?php _e( 'Format: database:username:password<br/>Enter up to 3 databases, separated by newlines' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="s3-push-folders"><?php _e( 'Folders To Push' ); ?></label></th>
				<td>
					<textarea name="s3-push-folders" id="s3-push-folders" cols="30" rows="10"><?php echo esc_textarea( $s3_backup['s3-push-folders'] ); ?></textarea>
					<br/>
					<span class="description"><?php _e( 'Up to 10 folders, separated by newlines' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="s3-rsync-bucket"><?php _e( 'Rsync Bucket' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="s3-rsync-bucket" id="s3-rsync-bucket" value="<?php echo esc_attr( $s3_backup['s3-rsync-bucket'] ); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="s3-rsync-folders"><?php _e( 'Folders To Rsync' ); ?></label></th>
				<td>
					<textarea class="regular-text" name="s3-rsync-folders" id="s3-rsync-folders" rows="3" cols="30"><?php echo esc_textarea( $s3_backup['s3-rsync-folders'] ); ?></textarea>
					<br/>
					<span class="description"><?php _e( 'Up to 3 folders, separated by newlines' ); ?></span>
				</td>
			</tr>
		</table>
		<?php if ( !empty ( $errors ) ) : ?>
			<div class="errors">
				<?php foreach ( $errors as $error ) : ?>
					<p class="s3-error">
						<?php echo esc_html( $error ); ?>
					</p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php
	}
}
