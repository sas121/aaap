<?php

class Synthesis_WP_Config {

	private $config_path;
	private $config_contents;
	private static $constant_names = array( 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST', 'DB_CHARSET', 'DB_COLLATE', 'AUTH_KEY',
		'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT',
		'NONCE_SALT', 'WPLANG', 'WP_DEBUG');

	public static function get_constant_names() {
		return self::$constant_names;
	}

	public function __construct( $config_path ) {
		$this->config_path = $config_path;
		$this->config_contents = file( $this->config_path );
		foreach ( $this->config_contents as $index => $line ) {
			$this->config_contents[$index] = trim( $line );
		}
	}

	public function update_config( $constants, $prefix = 'wp_' ) {
		foreach ( $this->config_contents as $index => $line ) {
			foreach ( $constants as $name => $new_value ) {
				if ( !is_scalar( $new_value ) ) {
					do_action( 'synthesis_clone_error', 'Non-scalar value provided for constant ' . $name );
				}
				if ( preg_match( '#define\s*\(\s*[\'"]' . $name . '[\'"]#', $line ) ) {
					if ( is_string( $new_value ) ) {
						// String value
						$new_value_serialized = "'" . self::esc_quotes( $new_value ) . "'";
					} elseif ( is_bool( $new_value ) ) {
						// Boolean value
						$new_value_serialized = $new_value ? 'true' : 'false';
					} else {
						// Numeric value
						$new_value_serialized = $new_value;
					}

					$this->config_contents[$index] = 'define( \'' . self::esc_quotes( $name ) . '\', ' . $new_value_serialized . ' );';
				}
			}

			if ( preg_match( '#table_prefix\s*=\s*[\'"][^\'"]+[\'"];#', $line ) ) {
				$this->config_contents[$index] = '$table_prefix = \'' . self::esc_quotes( $prefix ) . '\';';
			}
		}
		$this->write_config();
	}

	private function write_config() {
		$handle = fopen( $this->config_path, 'w' );
		fwrite( $handle, implode( $this->config_contents, "\n" ) );
		fclose( $handle );
	}

	public function check_config() {
		$table_prefix_count = 0;
		$found_warning = false;
		$found_error = false;

		$constants = array();
		foreach ( self::get_constant_names() as $constant_name ) {
			$constants[$constant_name] = array(
				'regex' => '#define\s*\(\s*[\'"]' . $constant_name . '#',
				'count' => 0
			);
		}

		// Go through each line of the config and check for definitions of constants and the table prefix
		foreach ( $this->config_contents as $line ) {
			// Ignore comment lines
			if ( strncmp($line, '//', 2 ) ) {
				foreach ( $constants as $constant_name => $constant_info ) {
					if ( preg_match( $constant_info['regex'], $line ) ) {
						$constants[$constant_name]['count']++;
					}
				}
				if ( preg_match( '#table_prefix\s*=\s*[\'"][^\'"]+[\'"];#', $line ) ) {
					$table_prefix_count++;
				}
			}
		}

		// Check for too few or too many definitions of each constant
		foreach ( $constants as $constant_name => $constant_info ) {
			if ( 0 == $constant_info['count'] ) {
				do_action( 'synthesis_clone_warning', "wp-config.php doesn't contain a definition for constant $constant_name" );
                $found_warning = true;
			} elseif ( $constant_info['count'] > 1 ) {
				do_action( 'synthesis_clone_warning', "wp-config.php contains multiple definitions for constant $constant_name" );
                $found_warning = true;
			}
		}

		// Check for too few or too many definitions of $table_prefix
		if ( 0 == $table_prefix_count ) {
			do_action( 'synthesis_clone_warning', 'wp-config.php doesn\'t contain a definition for $table_prefix' );
            $found_warning = true;
		} elseif ( $table_prefix_count > 1 ) {
			do_action( 'synthesis_clone_warning', 'wp-config.php contains multiple definitions for constant $table_prefix' );
            $found_warning = true;
		}


        // TODO: differentiate between error/warning in return
        if ( ! $found_error && ! $found_warning ) {
            do_action( 'synthesis_clone_success', 'No issues detected in wp-config.php' );
            return true;
        }

        return false;
	}

	private static function esc_quotes( $string, $string_quotes = "'" ) {
		return str_replace( "$string_quotes", "\\$string_quotes", str_replace( '\\', '\\\\', $string ) );
	}
}