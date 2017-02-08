<?php

class Synthesis_Deploy {

	/**
	 * Package an existing site
	 * @param string $package_file Path of the zip file where the packaged site should be created
	 * @param array $package_config The configuration for this package
	 * @return bool Whether the site was packaged successfully
	 */
	public static function package_site( $package_file, $package_config = array() ) {
		require_once( SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR . 'synthesis-zip.php' );
		require_once( SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR . 'synthesis-wp-config.php' );

		$site_config = new Synthesis_WP_Config( ABSPATH . 'wp-config.php' );
		$site_config->check_config();

		// TODO: sql backup
		$zip = Synthesis_Zip::create( $package_file );

		// TODO will@will: Nasty nested function signature logic. Clean up
		if ( isset( $package_config['excluded-files'] ) ) {
			if ( isset( $package_config['included-files' ] ) ) {
				$zip->zip_dir( ABSPATH, 'files', $package_config['excluded-files'], $package_config['included-files'] );
			} else {
				$zip->zip_dir( ABSPATH, 'files', $package_config['excluded-files'] );
			}
		} else {
			$zip->zip_dir( ABSPATH, 'files' );
		}

		do_action( 'synthesis_clone_line', "Writing package file" );
		$zip->close();

		return true;
	}

	/**
	 * Unpackage a packaged site
	 * @param string $package_file Path of the zip file where the packaged site is located
	 * @param array $deploy_config The configuration for this deployment
	 * @return bool Whether the packaged site was deployed successfully
	 */
	public static function unpackage_site( $package_file, $deploy_config = array() ) {
		require_once( SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR . 'synthesis-zip.php' );
		require_once( SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR . 'synthesis-wp-config.php' );

		$zip = Synthesis_Zip::open( $package_file );
		$zip->extract_dir( 'files', ABSPATH );
		$zip->close();

		$site_config = new Synthesis_WP_Config( ABSPATH . 'wp-config-pre.php' );
		$constants = ( isset( $deploy_config['wp-config'] ) && isset( $deploy_config['wp-config']['constant-values'] ) ) ? $deploy_config['wp-config']['constant-values'] : array();
		$prefix = isset( $deploy_config['wp-config'] ) && isset( $deploy_config['wp-config']['prefix'] ) ? $deploy_config['wp-config']['prefix'] : 'wp_';
		$site_config->update_config( $constants, $prefix );

		return true;
	}
}

// If we're running from WP CLI, add our CLI commands.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	class Synthesis_Deploy_Commands extends WP_CLI_Command {

		/**
		 * @synopsis --package=<package-file> [--conf=<config-file>] [--verbose] [--preview]
		 * @subcommand package
		 *
		 */
		public function package( $args, $assoc_args ) {

			if( $assoc_args['verbose'] ) {
				self::enable_verbose();
			}

			$verbose = isset( $assoc_args['preview'] );

			$config_path = self::get_value_or_default( $assoc_args, 'conf' );
			$config = self::get_config_with_overrides( self::get_base_package_config( $verbose ), $config_path, 'package', $args, $assoc_args );

			$result = Synthesis_Deploy::package_site( $assoc_args['package'], $config );
			$result ? do_action( 'synthesis_clone_success', "Site package completed successfully" ) : do_action( 'synthesis_clone_error', "Site package could not be completed" );

			self::disable_verbose();
		}

		/**
		 * @synopsis --package=<package-file> [--conf=<config-file>] [--verbose]
		 * @subcommand unpackage
		 */
		public function unpackage( $args, $assoc_args ) {

			if( $assoc_args['verbose'] ) {
				self::enable_verbose();
			}

			$config_path = self::get_value_or_default( $assoc_args, 'conf' );
			$config = self::get_config_with_overrides( self::get_base_unpackage_config(), $config_path, 'explode', $args, $assoc_args );
			$result = Synthesis_Deploy::unpackage_site( $assoc_args['package'], $config );
			$result ? do_action( 'synthesis_clone_success', "Site unpackage completed successfully" ) : do_action( 'synthesis_clone_error', "Site unpackage could not be completed" );

			self::disable_verbose();
		}

		/**
		 * Load a JSON config, gracefully handling errors such as non-existent paths and malformed JSON
		 *
		 * @param string $config_path The path to the config
		 * @param string $type the type of action
		 * @return bool|array The config, or false if it wasn't successfully loaded
		 */
		private static function load_config( $config_path, $type ) {
			if ( !file_exists( $config_path ) ) {
				WP_CLI::error( __( "Config path $config_path could not be found" ) );
				return false;
			}

			$config_contents = file_get_contents( $config_path );
			$config = json_decode( $config_contents, true );

			if ( null === $config ) {
				WP_CLI::error( __( "Config at path $config_path contains malformed JSON" ) );
				return false;
			}

			return $config;
		}

		private static function get_config_with_overrides( $base_config, $config_path, $type ) {
			$wp_config = self::get_wp_config();

			// Start with wp-config and allow base config to override
			$config = array_replace_recursive( $wp_config, $base_config );

			if ( file_exists( $config_path ) ) {
				$file_config = self::load_config( $config_path, $type );

				if ( $file_config ) {
					// Allow file config to override base and wp-config settings
					$config = array_replace_recursive( $config, $file_config );

					$default_config = array();
					$type_config = array();

					if ( isset( $config['default'] ) ) {
						$default_config = $config['default'];
					}

					if ( isset( $config[$type] ) ) {
						$type_config = $config[$type];
					}

					return array_replace_recursive( $default_config, $type_config );
				}
			} elseif ( $config_path ) {
				WP_CLI::error( "Specified config \"$config_path\" does not exist" );
			}

			return $config;
		}

		private static function get_wp_config() {
			require( SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR . '/synthesis-wp-config.php' );

			$constant_names = Synthesis_WP_Config::get_constant_names();
			$constants = get_defined_constants( true );
			if ( !isset( $constants['user'] ) ) {
				return array();
			}
			$config_values = array();
			foreach ( $constant_names as $key ) {
				if ( isset( $constants['user'][$key] ) ) {
					$config_values[$key] = $constants['user'][$key];
				}
			}
			return array(
				'default' => array(
					'wp-config' => array(
						'constant-values' => $config_values
					)
				)
			);

		}

		/**
		 * Get the default config for cloning a site
		 * @param bool $preview
		 * @return array The default config
		 */
		private static function get_base_package_config( $preview = false ) {
			if ( $preview ) {
				$last_month = strtotime( '-1 Month' );
				$default = array(
					'excluded-files' => array(
						'wp-content/uploads'
					),
					'included-files' => array(
						'wp-content/uploads/' . date( 'Y/m' ),
						'wp-content/uploads/' . date( 'Y/m', $last_month )
					)
				);
			} else {
				$default = array();
			}
			return apply_filters( 'synth_deploy_base_package_config', $default, $preview );
		}

		/**
		 * Get the default config for deploying a cloned site
		 * @return array The default config
		 */
		private static function get_base_unpackage_config() {
			return array();
		}

		private static function get_value_or_default( $array, $key, $default=false ) {
			return isset( $array[$key] ) ? $array[$key] : $default;
		}

		private static function enable_verbose() {
			// Output any error/warning/success messages to the CLI
			add_action( 'synthesis_clone_success', array( 'WP_CLI', 'success' ) );
			add_action( 'synthesis_clone_line', array( 'WP_CLI', 'line' ) );
		}

		private static function disable_verbose() {
			remove_action( 'synthesis_clone_success', array( 'WP_CLI', 'success' ) );
			remove_action( 'synthesis_clone_line', array( 'WP_CLI', 'line' ) );
		}
	}

	WP_CLI::add_command( 'synth-deploy', 'Synthesis_Deploy_Commands' );

	add_action( 'synthesis_clone_warning', array( 'WP_CLI', 'warning' ) );
	add_action( 'synthesis_clone_error', array( 'WP_CLI', 'error' ) );
}
