<?php
if ( !class_exists( 'Synthesis_DB_Backup' ) ) {
	class Synthesis_DB_Backup {

		const RECORDS_PER_QUERY = 10000;
		const BACKUP_FOLDER = 'synthesis-db-backups';

		const IS_BACKUP_RUNNING_OPTION = 'synthesis_db_backup_is_running';
		const IS_RESTORE_RUNNING_OPTION = 'synthesis_db_restore_is_running';
		const DB_SNAPSHOT_OPTION_NAME = 'synthesis-db-snapshot';

		const HALT_BACKUP_FILE = 'halt_backup';
		const HALT_RESTORE_FILE = 'halt_restore';
		const MAX_TABLE_SIZE = 8000000;

		const BACKUP_STATUS_FILE_NAME = 'status.json';
		const RESTORE_STATUS_FILE_NAME = 'restore-status.json';

		const VERSION = 1;

		const FULL_SUFFIX = '_full.sql';
		const DROP_NEW_SUFFIX = '_drop_new.sql';
		const DROP_ORIGINAL_SUFFIX = '_drop_original.sql';
		const CREATE_SUFFIX = '_create.sql';
		const RENAME_SUFFIX = '_rename.sql';
		const DATA_SUFFIX_TEMPLATE = '_data-%s.sql';

		static $backup_id;

		/**
		 * Executes a SQL query and returns the results as an associative array.
		 *
		 * @param $query string SQL Query to execute
		 * @return mixed Database results are an associative array in the format array("column_name" => "data_value")
		 */
		static function execute_query( $query ) {
			/** @global wpdb wpdb */
			global $wpdb;

			return $wpdb->get_results( $query, ARRAY_A );
		}

		static function get_col( $query, $col = 0 ) {
			/** @global wpdb wpdb */
			global $wpdb;

			return $wpdb->get_col( $query, $col );
		}

		static function get_var( $query = null, $row = 0, $col = 0 ) {
			/** @global wpdb wpdb */
			global $wpdb;

			return $wpdb->get_var( $query, $col, $row );
		}

		/**
		 * @param string                  $original_table_name  string Name of the table to backup
		 * @param string                  $output_file_dir      string Full path to output table backup script to.
		 * @param Synthesis_Backup_Status $status               array  The current status array, to which status updates will be added
		 * @return string             Backup Result
		 */
		private static function export_table( $original_table_name, $output_file_dir, $status ) {
			/** @global wpdb wpdb */
			global $wpdb;

			if ( empty( $output_file_dir ) || empty( $original_table_name ) ) {
				return "skipped";
			}

			$is_option_table = $original_table_name == $wpdb->options;


			// If this is the option table, we need to be sure to inject the current backup data
			$need_backup_data_injected = $is_option_table;

			// Restore rework related variables
			$new_table_name = $original_table_name . '_' . self::$backup_id;
			$table_file_base = $output_file_dir . str_replace( '``', '`', $original_table_name );
			$unified_table_file = $table_file_base . self::FULL_SUFFIX;
			$drop_new_file = $table_file_base . self::DROP_NEW_SUFFIX;
			$drop_original_file = $table_file_base . self::DROP_ORIGINAL_SUFFIX;
			$create_file = $table_file_base . self::CREATE_SUFFIX;
			$rename_file = $table_file_base . self::RENAME_SUFFIX;
			$data_file_template = $table_file_base . self::DATA_SUFFIX_TEMPLATE;

			// Create the sql file that drops the temporary database
			$fd = fopen( $drop_new_file, 'w' );
			$full_file_handle = fopen( $unified_table_file, 'w' );
			$drop_statement = "DROP TABLE IF EXISTS `$new_table_name`;\n";
			fwrite( $fd, $drop_statement );
			fwrite( $full_file_handle, $drop_statement );
			fclose( $fd );

			// Get a sql create statement for the table
			$create_statement = self::get_var( "SHOW CREATE TABLE `$original_table_name`;", 0, 1 );

			// Replace the first line of the Create statement with a create for the correct table.
			$create_lines = explode( "\n", $create_statement );
			$create_lines[0] = "CREATE TABLE `$new_table_name` (";
			$create_statement = implode( "\n", $create_lines ) . ";\n\n";

			// Write the create statement
			$fd = fopen( $create_file, 'w' );
			fwrite( $fd, $create_statement );
			fwrite( $full_file_handle, $create_statement );
			fclose( $fd );

			// Read all rows, but do it in self::RECORD_LIMIT chunks, for performance reasons
			$query = sprintf( 'SELECT * FROM `%s`', $original_table_name ) . ' LIMIT %d, %d';

			$limit_start = 0;
			$num_records = self::RECORDS_PER_QUERY;

			$data = self::execute_query( $wpdb->prepare( $query, $limit_start, $num_records ) );
			for ( $i = 0; !empty( $data ); $i++ ) {
				// Exit if the "halt" file exists
				if ( self::is_backup_halt_requested() ) {
					return "halted";
				}

				// Write these results to their own file
				$fd = fopen( sprintf( $data_file_template, $i ), 'w' );
				// Write our insert statement
				$insert_statement = "INSERT INTO `$new_table_name` VALUES";
				fwrite( $fd, $insert_statement );
				fwrite( $full_file_handle, $insert_statement );

				// Count the number of rows for logging purposes.
				$count = 0;

				// Write out each individual row
				$first = true;
				foreach ( $data as $row ) {
					$count++;

					// Backing up the options table requires some special casing for our plugin
					if ( $is_option_table && isset( $row['option_name'] ) ) {
						// Skip our transient settings
						if ( self::is_db_snapshot_transient( $row['option_name'] ) ) {
							continue;
						} else if ( $row['option_name'] == self::DB_SNAPSHOT_OPTION_NAME ) {
							if ( isset( $row['option_value'] ) ) {
								// Already inserting the backup data,
								$need_backup_data_injected = false;
								$row['option_value'] = serialize( $status->get_snapshot_info() );
							}
						}
					}

					// After the first row, add a comma separator
					if ( !$first ) {
						fwrite( $fd, "," );
						fwrite( $full_file_handle, "," );
					}
					$first = false;

					// Write out a single row of data
					$data_row = "\n(" . implode( ',', array_map( array( __CLASS__, 'sanitize_sql_value' ), $row ) ) . ")";
					fwrite( $fd, $data_row );
					fwrite( $full_file_handle, $data_row );
				}

				// Append the final semi-colon
				fwrite( $fd, ";\n\n" );
				fwrite( $full_file_handle, ";\n\n" );

				// Close this data file
				fclose( $fd );

				$limit_start += $count;
				$status->set_table_current_row( $original_table_name, $limit_start );
				$status->write();

				self::increase_time_limit();

				$data = self::execute_query( $wpdb->prepare( $query, $limit_start, $num_records ) );
			}

			// Check to see if we still need to inject the backup data.
			if ( $need_backup_data_injected ) {
				$insert_statement =
					sprintf(
						"INSERT INTO `%s` (option_name, option_value) VALUES ('%s', %s);\n\n",
						$new_table_name,
						self::DB_SNAPSHOT_OPTION_NAME,
						self::sanitize_sql_value( serialize( $status->get_snapshot_info() ) )
					);
				$fd = fopen( sprintf( $data_file_template, $i ), 'w' );
				fwrite( $fd, $insert_statement );
				fwrite( $full_file_handle, $insert_statement );
				fclose( $fd );
			}

			// Drop the original table, so we can replace it with the restored backup.
			$fd = fopen( $drop_original_file, 'w' );
			$drop_statement = "DROP TABLE IF EXISTS `$original_table_name`;\n";
			fwrite( $fd, $drop_statement );
			fwrite( $full_file_handle, $drop_statement );
			fclose( $fd );

			// Give our new table the old name
			$fd = fopen( $rename_file, 'w' );
			$rename_statement = "RENAME TABLE `$new_table_name` TO `$original_table_name`;";
			fwrite( $fd, $rename_statement );
			fwrite( $full_file_handle, $rename_statement );
			fclose( $fd );

			// Close the unified file
			fclose( $full_file_handle );

			return "finished";
		}

		public static function create_restore_status_directory( $backup_id ) {
			$backup_status_dir = self::get_restore_status_directory( $backup_id );
			mkdir( $backup_status_dir, 0755, true );

			return $backup_status_dir;
		}

		public static function import_table( $backup_id, $table_name ) {
			/** @global wpdb wpdb */
			global $wpdb;

			// Increase the PHP time limits
			self::increase_php_limits( true );

			// Load the backup status to get total row count etc.
			$backup_status = self::load_backup_status_from_id( $backup_id );

			// Defaults in case we can't load this data.
			$total_row_count = $backup_status->get_table_row_count( $table_name, 0 );
			$records_per_query = $backup_status->get_records_per_query( 0 );

			// Create a file for recording the status of the restore
			$restore_status_dir = self::create_restore_status_directory( $backup_id );
			$restore_status_file = $restore_status_dir . $table_name . ".json";

			$status = array(
				'table'               => $table_name,
				'status'              => 'pending',
				'current_row'         => 0,
				'rows'                => $total_row_count,
				'restore_status_file' => $restore_status_file,
			);

			// Write the current status
			self::save_restore_status( $restore_status_file, $status );

			$table_dir = self::get_backup_dir( $backup_id ) . 'tables/' . $table_name;
			$create_file = $table_dir . '/' . $table_name . self::CREATE_SUFFIX;
			$drop_new_file = $table_dir . '/' . $table_name . self::DROP_NEW_SUFFIX;
			$drop_original_file = $table_dir . '/' . $table_name . self::DROP_ORIGINAL_SUFFIX;
			$rename_file = $table_dir . '/' . $table_name . self::RENAME_SUFFIX;
			$data_files = glob( $table_dir . '/' . $table_name . sprintf( self::DATA_SUFFIX_TEMPLATE, '*' ) );

			if ( self::is_table_restore_halt_requested( $backup_id, $table_name ) ) {
				$status['status'] = 'cancelled';
				self::save_restore_status( $restore_status_file, $status );
				return;
			}

			self::increase_time_limit( true );

			// Drop new table
			$drop_new_sql = file_get_contents( $drop_new_file );
			$wpdb->query( $drop_new_sql );

			// Create new table
			$create_sql = file_get_contents( $create_file );
			$wpdb->query( $create_sql );

			$current_row_count = 0;
			// Load data
			foreach ( $data_files as $data_file ) {
				if ( self::is_table_restore_halt_requested( $backup_id, $table_name ) ) {
					// Clean up partial table
					$status['status'] = 'cancelled';
					self::save_restore_status( $restore_status_file, $status );

					$drop_new_sql = file_get_contents( $drop_new_file );
					$wpdb->query( $drop_new_sql );
					return;
				}
				$status['status'] = 'restoring data';
				self::save_restore_status( $restore_status_file, $status );

				$data_sql = file_get_contents( $data_file );
				$wpdb->query( $data_sql );

				$current_row_count += $records_per_query;
				$status['current_row'] = min( $total_row_count, $current_row_count ); // Update the status

				self::save_restore_status( $restore_status_file, $status );

				self::increase_php_limits( true );
			}

			// Drop old table
			$drop_original_sql = file_get_contents( $drop_original_file );
			$wpdb->query( $drop_original_sql );

			// Rename new table
			$status['status'] = 'restoring original table';
			self::save_restore_status( $restore_status_file, $status );

			$rename_sql = file_get_contents( $rename_file );
			$wpdb->query( $rename_sql );

			$status['status'] = 'restored';
			self::save_restore_status( $restore_status_file, $status );
		}

		// Skip data about backup and restores running.
		private static function is_db_snapshot_transient( $option_name ) {
			$options = array( self::IS_BACKUP_RUNNING_OPTION, self::IS_RESTORE_RUNNING_OPTION );

			foreach ( $options as $option ) {
				if ( ( '_transient_' . $option == $option_name ) || ( '_transient_timeout_' . $option == $option_name ) ) {
					return true;
				}
			}

			return false;
		}


		/**
		 * Sanitizes a SQL string value
		 *
		 * @param $value mixed Value to sanitize
		 * @return bool|null|string Sanitized value
		 */
		private static function sanitize_sql_value( $value ) {
			/** @global wpdb wpdb */
			global $wpdb;

			return $wpdb->prepare( "%s", $value );
		}

		/**
		 * @param $excluded_tables     array Which tables to exclude from exporting. Null or empty array to exclude none
		 * @param $include_tables      array Which tables to include (before considering exclusions. Null to include all
		 * @param $time_limit_in_hours int How long to make the PHP execution time limit
		 * @return array Information about the database snapshot
		 */
		static function export_database( $excluded_tables = null, $include_tables = null, $time_limit_in_hours = 2 ) {
			require_once( 'synthesis-backup-status.php' );

			$synthesis_backup_dir = self::get_backup_dir();
			$synthesis_backup_dir_index = $synthesis_backup_dir . 'index.html';

			if ( !file_exists( $synthesis_backup_dir ) ) {
				mkdir( $synthesis_backup_dir, 0755, true );
			}

			if ( !file_exists( $synthesis_backup_dir_index ) ) {
				$index = fopen( $synthesis_backup_dir_index, 'w' );
				fclose( $index );
			}

			// Create a unique GUID for this backup.
			$backup_id = wp_generate_password( 12, false, false );
			self::$backup_id = $backup_id;

			$backup_dir = $synthesis_backup_dir . $backup_id . '/';
			$backup_dir_tables = $backup_dir . 'tables/';

			if ( !file_exists( $backup_dir ) ) {
				mkdir( $backup_dir, 0755 );
			}

			if ( !file_exists( $backup_dir_tables ) ) {
				mkdir( $backup_dir_tables );
			}

			self::increase_php_limits( false, $time_limit_in_hours );

			$tables = $include_tables;
			if ( is_null( $tables ) ) {
				$tables = self::get_col( 'SHOW TABLES' );
			}

			if ( is_null( $excluded_tables ) ) {
				$excluded_tables = array();
			}
			$tables = array_diff( $tables, $excluded_tables );

			$status_path = $backup_dir . self::BACKUP_STATUS_FILE_NAME;

			$status = new Synthesis_Backup_Status( $status_path );


			foreach ( $tables as $raw_table ) {
				// Tables can theoretically contain backticks, so they must be escaped
				$table = str_replace( '`', '``', $raw_table );

				// Get the number of rows in the table.
				$row_count = self::get_var( "SELECT COUNT(*) FROM `$table`" );

				// Tables start as pending...
				$status_code = Synthesis_Backup_Status::TABLE_STATUS_PENDING;
				if ( $row_count > self::MAX_TABLE_SIZE ) {
					// ... unless there are too many rows
					$status_code = Synthesis_Backup_Status::BACKUP_STATUS_SKIPPED;
				}

				// Add this table to the status file.
				$status->add_table( $raw_table, $status_code, $row_count );
			}

			$backup_timestamp = current_time( 'timestamp' );

			$status->set_start( $backup_timestamp );
			$status->write();


			$backup_zip = 'backup_' . date( 'm-d-Y_H-i-s', current_time( 'timestamp' ) ) . '.zip';
			$backup_zip_path = $backup_dir . $backup_zip;

			$snapshot_info = array(
				'path'      => $backup_zip_path,
				'url'       => self::get_backup_url() . '/' . $backup_id . '/' . $backup_zip,
				'timestamp' => $backup_timestamp,
				'id'        => $backup_id,
			);

			$status->set_snapshot_info( $snapshot_info );

			// Create our Zip File
			$zip = new ZipArchive();
			$zip->open( $backup_zip_path, ZIPARCHIVE::CREATE );

			// Backup tables
			foreach ( $tables as $table ) {
				if ( self::is_backup_halt_requested() ) {
					$status->set_status_code( Synthesis_Backup_Status::BACKUP_STATUS_CANCELLED );
					break;
				} else if ( $status->get_table_status_code( $table ) == Synthesis_Backup_Status::TABLE_STATUS_SKIPPED ) {
					// Don't backup tables we've decided to skip
					continue;
				}

				$status->set_table_status_code( $table, Synthesis_Backup_Status::TABLE_STATUS_PROCESSING );
				$status->set_table_start( $table, current_time( 'timestamp' ) );
				$status->write();

				$export_path = $backup_dir . 'tables/' . $table . '/';
				mkdir( $export_path );
				$table_result = self::export_table( $table, $export_path, $status );

				$status->set_table_status_code( $table, $table_result );
				$status->set_table_end( $table, current_time( 'timestamp' ) );
				$status->write();

				$full_path = trailingslashit( $export_path ) . $table . self::FULL_SUFFIX;

				$zip->addFile( $full_path, $table . '.sql' );
			}

			$status->set_end( current_time( 'timestamp' ) );
			$status->set_status_code( Synthesis_Backup_Status::BACKUP_STATUS_FINISHED );
			$status->write();

			$zip->close();

			self::cleanup_old_folders();
			self::end_backup();

			// Force a write to update the timestamp.
			$status->write( true );

			return $snapshot_info;
		}

		/**
		 * @param int $limit
		 */
		static function cleanup_old_folders( $limit = 3 ) {
			$backup_folders = array_keys( self::list_backups() );
			$backup_dir = self::get_backup_dir();

			while ( count( $backup_folders ) > $limit ) {
				$folder = array_shift( $backup_folders );
				self::delete_backup_folder( $backup_dir . $folder );
			}
		}

		static function delete_backup_folder( $path ) {
			$slashed_path = trailingslashit( $path );
			$folder = opendir( $path );
			while ( $entry = readdir( $folder ) ) {
				$entry_path = $slashed_path . $entry;
				if ( $entry != '.' && $entry != '..' ) {
					if ( is_dir( $entry_path ) ) {
						self::delete_backup_folder( $entry_path );
					} else {
						unlink( $entry_path );
					}
				}
			}
			closedir( $folder );
			rmdir( $path );
		}

		/**
		 * Gets a list of existing backups sorted by start time
		 *
		 * @return array Key/value pairs of backup IDs and timestamps
		 */
		static function list_backups() {
			$backup_dir = self::get_backup_dir();
			$dir = opendir( $backup_dir );
			$backup_folders = array();
			while ( $entry = readdir( $dir ) ) {
				$entry_path = $backup_dir . $entry;
				if ( $entry != '.' && $entry != '..' && $entry != 'index.html' && is_dir( $entry_path ) ) {
					$status_path = $entry_path . '/' . self::BACKUP_STATUS_FILE_NAME;
					if ( file_exists( $status_path ) ) {
						$status_text = file_get_contents( $status_path );
						$status_json = json_decode( $status_text, true );
						$backup_folders[$entry] = $status_json['start'];
					} else {
						$backup_folders[$entry] = 0;
					}
				}
			}
			asort( $backup_folders, SORT_NUMERIC );
			return $backup_folders;
		}

		/**
		 * Gets a list of recommended tables to exclude from your database backup.
		 *
		 * @return array Comma separated list of strings.
		 */
		static function get_default_excluded_tables() {
			/** @global wpdb wpdb */
			global $wpdb;

			// Backing up posts is not the intended use of this plugin. By default, we'll exclude anything post related.
			return array( $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->commentmeta, $wpdb->term_relationships );
		}

		static function get_backup_dir( $backup_id = false ) {
			$upload_dir = wp_upload_dir();
			$synthesis_backup_dir = trailingslashit( $upload_dir['basedir'] ) . self::BACKUP_FOLDER . '/';
			if ( $backup_id ) {
				$synthesis_backup_dir = trailingslashit( $synthesis_backup_dir . $backup_id );
			}
			return $synthesis_backup_dir;
		}

		static function get_backup_url() {
			$upload_dir = wp_upload_dir();
			$synthesis_backup_dir = trailingslashit( $upload_dir['baseurl'] ) . self::BACKUP_FOLDER . '/';
			return trailingslashit( $synthesis_backup_dir );
		}

		static function get_restore_status_directory( $backup_id ) {
			return self::get_backup_dir( $backup_id ) . 'restore-status/';
		}

		/**
		 * @param int  $time_limit_in_hours How many hours to increase the limit to. Defaults to 2.
		 * @param bool $restore             True if we're doing a restore
		 */
		protected function increase_php_limits( $restore = false, $time_limit_in_hours = 2 ) {
			// Don't abort script if the client connection is lost/closed
			ignore_user_abort( true );

			// 2 hour execution time limits
			ini_set( 'default_socket_timeout', 60 * 60 * $time_limit_in_hours );
			self::increase_time_limit( $restore, $time_limit_in_hours );

			// Increase the memory limit
			$current_memory_limit = trim( @ini_get( 'memory_limit' ) );

			if ( preg_match( '/(\d+)(\w*)/', $current_memory_limit, $matches ) ) {
				$current_memory_limit = $matches[1];
				$unit = $matches[2];

				// Up memory limit if currently lower than 256M
				if ( 'g' !== strtolower( $unit ) ) {
					if ( ( $current_memory_limit < 256 ) || ( 'm' !== strtolower( $unit ) ) )
						ini_set( 'memory_limit', '256M' );
				}
			} else {
				// Couldn't determine current limit, set to 256M to be safe
				ini_set( 'memory_limit', '256M' );
			}
		}

		protected function increase_time_limit( $restore = false, $time_limit_in_hours = 2 ) {
			set_time_limit( 60 * 60 * $time_limit_in_hours );

			if ( $restore ) {
				self::continue_restore();
			} else {
				self::continue_backup();
			}
		}

		/**
		 * @param $status_path
		 * @return array|mixed
		 */
		public static function read_status_file( $status_path ) {
			if ( file_exists( $status_path ) ) {
				$contents = file_get_contents( $status_path );
				$json = json_decode( $contents, true );
				if ( $json ) {
					return $json;
				}
			}
			return array();
		}

		/**
		 * Load the status for a given ID.
		 * @param string $backup_id ID to load status for.
		 * @return Synthesis_Backup_Status Status object
		 */
		public static function load_backup_status_from_id( $backup_id ) {
			require_once( 'synthesis-backup-status.php' );

			$status_file = self::get_backup_dir( $backup_id ) . '/' . self::BACKUP_STATUS_FILE_NAME;
			return new Synthesis_Backup_Status( $status_file );
		}

		public static function load_restore_status_from_id( $backup_id ) {
			$status_dir = self::get_restore_status_directory( $backup_id );

			$restore_status = array();
			$status_files = glob( $status_dir . '/*.json' );
			foreach ( $status_files as $status_file ) {
				$status = self::read_status_file( $status_file );
				if ( isset( $status['table'] ) ) {
					$restore_status[$status['table']] = $status;
				}
			}
			return $restore_status;
		}

		/**
		 * @param $status_path
		 * @param $status array
		 */
		public static function save_restore_status( $status_path, $status ) {

			$status['version'] = self::VERSION;
			$status['last_update'] = current_time( 'timestamp' );

			$encoded = json_encode( $status );

			$status = fopen( $status_path, 'w' );
			fwrite( $status, $encoded, strlen( $encoded ) );
			fclose( $status );
		}

		public static function continue_backup() {
			set_transient( self::IS_BACKUP_RUNNING_OPTION, self::$backup_id, 5 * 60 );
		}

		public static function continue_restore() {
			set_transient( self::IS_RESTORE_RUNNING_OPTION, self::$backup_id, 5 * 60 );
		}

		public static function end_backup() {
			delete_transient( self::IS_BACKUP_RUNNING_OPTION );
		}

		public static function end_restore() {
			delete_transient( self::IS_RESTORE_RUNNING_OPTION );
		}

		public static function is_backup_running() {
			$running = get_transient( self::IS_BACKUP_RUNNING_OPTION );
			return empty( $running ) ? false : $running;
		}

		public static function is_restore_running() {
			$running = get_transient( self::IS_RESTORE_RUNNING_OPTION );
			return empty( $running ) ? false : $running;
		}

		public static function is_backup_halt_requested() {
			return file_exists(
				self::get_backup_dir()
				. trailingslashit( self::$backup_id )
				. self::HALT_BACKUP_FILE );
		}

		public static function is_table_restore_halt_requested( $backup_id, $table_name ) {
			$table_halt_file = trailingslashit( self::get_backup_dir( $backup_id ) ) . 'tables/' . $table_name . '/' . self::HALT_RESTORE_FILE;
			if ( file_exists( $table_halt_file ) ) {
				unlink( $table_halt_file );
				return true;
			}
		}

		public static function halt_table_restore( $backup_id, $table_name ) {
			$restore_status = self::load_restore_status_from_id( $backup_id );
			if ( isset( $restore_status[$table_name]['status'] ) && 'cancelled' != $restore_status[$table_name]['status'] ) {
				touch( trailingslashit( self::get_backup_dir( $backup_id ) ) . 'tables/' . $table_name . '/' . self::HALT_RESTORE_FILE );
			}
		}
	}
}
