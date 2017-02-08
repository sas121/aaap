<?php

class Synthesis_Backup_Status {
	const VERSION = 1.0;

	const STATUS_WRITE_EVENT = 'synthesis-backup-status-write';

	// Overall backup status keys
	const BACKUP_STATUS_STARTED = 'started';
	const BACKUP_STATUS_CANCELLED = 'cancelled';
	const BACKUP_STATUS_FINISHED = 'finished';

	// Table Status Keys
	const TABLE_STATUS_PENDING = 'pending';
	const TABLE_STATUS_SKIPPED = 'skipped';
	const TABLE_STATUS_PROCESSING = 'processing';

	// Status Keys
	const VERSION_KEY = 'version';
	const TABLE_KEY = 'tables';
	const RECORDS_PER_QUERY_KEY = 'records_per_query';
	const SNAPSHOT_INFO_KEY = 'snapshot-info';

	// Shared Keys
	const STATUS_CODE_KEY = 'status';
	const START_KEY = 'start';
	const END_KEY = 'end';
	const LAST_UPDATE_KEY = 'last_update';

	// Table Status Keys
	const CURRENT_ROW_KEY = 'current_row';
	const TOTAL_ROWS_KEY = 'rows';

	// Container for status information
	protected $status = array();

	// File to store the status in.
	protected $status_file;

	private $loaded_from_file = false;

	/**
	 * @var bool Determines if the file status has changed.
	 */
	private $dirty = false;

	/**
	 * Create a status object.
	 * If a file is given, and is non-empty, status is loaded from the file.
	 * Otherwise, an empty object is created
	 *
	 * @param null   $status_file
	 */
	public function __construct( $status_file = null ) {

		if ( !empty( $status_file ) ) {
			$this->set_status_file( $status_file );

			// Try to read status from the file.
			if ( $this->refresh() ) {
				return;
			}
		}

		// Initialize status array.
		$this->status = array();
		$this->status[self::VERSION_KEY] = self::VERSION;
	}

	/**
	 * If the status has changed, write it to the disk.
	 */
	public function __destruct() {
		if ( $this->dirty ) {
			$this->write();
		}
	}

	public function set_status_file( $file_name ) {
		$this->status_file = $file_name;
	}

	public function get_status_file() {
		return $this->status_file;
	}

	/**
	 * Sets a value in our status array.
	 *
	 * @param $key   mixed Key to set in the array
	 * @param $value mixed Value to set
	 */
	private function set_status( $key, $value ) {
		$this->dirty = true;
		$this->status[$key] = $value;
	}

	/**
	 * Determine if status exists for a given table
	 *
	 * @param string $table_name Table to find status for.
	 * @return bool Whether or not there is status for the table.
	 */
	public function contains_table( $table_name ) {
		return
			!empty( $this->status )
			&& !empty( $this->status[self::TABLE_KEY] )
			&& isset( $this->status[self::TABLE_KEY][$table_name] );
	}

	/**
	 * Set the status for a table. Create it if it doesn't exist.
	 *
	 * @param string       $table_name Name of the table
	 * @param string       $key        Key to set for the table
	 * @param string|mixed $value      Value to assign
	 */
	private function set_table_status( $table_name, $key, $value ) {
		// Mark the class as needing a write
		$this->dirty = true;

		// Create the entry for the table, if it doesn't exist
		if ( !$this->contains_table( $table_name ) ) {
			$this->add_table( $table_name );
		}

		// Set the final value
		$this->status['tables'][$table_name][$key] = $value;
	}

	/**
	 * Get the status for a table key, or a given default value (defaults to false)
	 *
	 * @param string $table_name Name of the table to get status for
	 * @param string $key        Key to set
	 * @param bool   $default    default value
	 * @return bool|mixed The value for the status, or default.
	 */
	private function get_table_status( $table_name, $key, $default = false ) {
		if ( isset( $this->status['tables'] ) && isset( $this->status['tables'][$table_name] ) ) {
			return self::get_value_or_default( $this->status['tables'][$table_name], $key, $default );
		}

		return $default;
	}

	/**
	 * Get the value from an associative array, or some default value.
	 *
	 * @param $container array Associative array to search
	 * @param $key       mixed Index to find in the array
	 * @param $default   mixed Default value to return
	 * @return mixed Value of $container[$key], or default, if it's not set
	 */
	private static function get_value_or_default( $container, $key, $default = false ) {
		if ( $container == null ) {
			return $default;
		}

		return isset( $container[$key] ) ? $container[$key] : $default;
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private function get_status_value( $key, $default = false ) {
		return self::get_value_or_default( $this->status, $key, $default );
	}

	public function get_records_per_query( $default = false ) {
		return $this->get_status_value( self::RECORDS_PER_QUERY_KEY, $default );
	}

	public function set_records_per_query( $value ) {
		$this->set_status( self::RECORDS_PER_QUERY_KEY, $value );
	}

	public function get_status_code() {
		return $this->get_status_value( self::STATUS_CODE_KEY );
	}

	public function set_status_code( $value ) {
		$this->set_status( self::STATUS_CODE_KEY, $value );
	}

	public function get_start() {
		return $this->get_status_value( self::START_KEY );
	}

	public function set_start( $value ) {
		$this->set_status( self::START_KEY, $value );
	}

	public function get_snapshot_info() {
		return $this->get_status_value( self::SNAPSHOT_INFO_KEY );
	}

	public function set_snapshot_info( $value ) {
		$this->set_status( self::SNAPSHOT_INFO_KEY, $value );
	}

	public function get_end() {
		return $this->get_status_value( self::END_KEY );
	}

	public function set_end( $value ) {
		$this->set_status( self::END_KEY, $value );
	}

	/**
	 * Add a table to the status
	 *
	 * @param string $table_name  Name of the table.
	 * @param string $status_code Current status.
	 * @param null   $row_count   Total number of rows in the table.
	 * @param int    $start       Start time
	 * @param null   $current_row Row currently being processed.
	 * @param int    $end         End time
	 */
	public function add_table( $table_name,
	                           $status_code = self::TABLE_STATUS_PENDING,
	                           $row_count = null,
	                           $start = 0,
	                           $current_row = null,
	                           $end = 0
	) {
		if ( !isset( $this->status[self::TABLE_KEY] ) ) {
			$this->set_status( self::TABLE_KEY, array() );
		}

		// Don't overwrite the entry if it already exists
		if ( empty( $this->status[self::TABLE_KEY][$table_name] ) ) {
			// Start empty
			$this->status[self::TABLE_KEY][$table_name] = array();

			$this->set_table_status_code( $table_name, $status_code );
			$this->set_table_start( $table_name, $start );
			$this->set_table_end( $table_name, $end );
			$this->set_table_current_row( $table_name, $current_row );
			$this->set_table_row_count( $table_name, $row_count );
		}
	}

	public function get_table_status_code( $table_name ) {
		return $this->get_table_status( $table_name, self::STATUS_CODE_KEY );
	}

	public function set_table_status_code( $table_name, $value ) {
		$this->set_table_status( $table_name, self::STATUS_CODE_KEY, $value );
	}

	public function get_table_start( $table_name ) {
		return self::get_table_status( $table_name, self::START_KEY );
	}

	public function set_table_start( $table_name, $value ) {
		$this->set_table_status( $table_name, self::START_KEY, $value );
	}

	public function get_table_end( $table_name ) {
		return self::get_table_status( $table_name, self::END_KEY );
	}

	public function set_table_end( $table_name, $value ) {
		$this->set_table_status( $table_name, self::END_KEY, $value );
	}

	public function get_table_current_row( $table_name, $default = false ) {
		return self::get_table_status( $table_name, self::CURRENT_ROW_KEY, $default );
	}

	public function set_table_current_row( $table_name, $value ) {
		$this->set_table_status( $table_name, self::CURRENT_ROW_KEY, $value );
	}

	public function get_table_row_count( $table_name, $default = false ) {
		return self::get_table_status( $table_name, self::TOTAL_ROWS_KEY, $default );
	}

	public function set_table_row_count( $table_name, $value ) {
		$this->set_table_status( $table_name, self::TOTAL_ROWS_KEY, $value );
	}

	public function get_table_names() {
		$tables = $this->get_status_value( self::TABLE_KEY, array() );

		return array_keys( $tables );
	}

	public function set_version( $value = self::VERSION ) {
		$this->set_status( self::VERSION_KEY, $value );
	}

	public function get_version() {
		return $this->get_status_value( self::VERSION_KEY, self::VERSION );
	}

	public function is_loaded_from_file() {
		return $this->loaded_from_file;
	}

	/**
	 * Reload the status from it's source
	 */
	public function refresh() {
		if ( !empty( $this->status_file ) ) {
			if ( file_exists( $this->status_file ) ) {
				$status_text = file_get_contents( $this->status_file );
				$status_json = json_decode( $status_text, true );

				if ( $status_json ) {
					// Version 0 had no version info. It is compatible with the current version, but doesn't support
					// restore functionality
					if ( !isset( $status_json[self::VERSION_KEY] )
						|| $status_json[self::VERSION_KEY] == self::VERSION
					) {
						$this->loaded_from_file = true;
						$this->status = $status_json;
						return $this->status;
					}
				}
			}

			// We were unable to read the status file
			return false;
		}

		return false;
	}

	/**
	 * Write the status to disk. By default, the status will only be written if a change is detected.
	 *
	 * @param bool $force Force a write, even if no change is detected (updates timestamp)
	 */
	public function write( $force = false ) {
		if ( $force || $this->dirty ) {
			// Update the last write-time
			$this->status[self::LAST_UPDATE_KEY] = current_time( 'timestamp' );

			$file = fopen( $this->status_file, 'w' );
			fwrite( $file, json_encode( $this->status ) );
			fclose( $file );
		}
	}
}