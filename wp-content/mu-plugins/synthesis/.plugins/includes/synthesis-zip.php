<?php

class Synthesis_Zip {

	private $zip_path;
	private $zip_archive;

	/**
	 * Create a new Synthesis_Zip instance for a specific zip path
	 * @param string $zip_path
	 * @param bool $create Whether to create a new path or open an existing one
	 */
	private function __construct( $zip_path, $create ) {
		$this->zip_path = str_replace( '\\', '/', $zip_path );

		$this->zip_archive = new ZipArchive();
		if ( $create ) {
			if ( file_exists( $this->zip_path ) ) {
				do_action( 'synthesis_clone_success', 'Overwriting existing zip archive' );
				unlink( $this->zip_path );
			}
			$this->zip_archive->open( $this->zip_path, ZIPARCHIVE::CREATE );
		} else {
			$this->zip_archive->open( $this->zip_path );
		}
	}

	/**
	 * Create a new zip archive
	 * @param string $zip_path The path where the new archive should be created
	 * @return Synthesis_Zip
	 */
	public static function create( $zip_path ) {
		return new Synthesis_Zip( $zip_path, true );
	}

	/**
	 * Open an existing zip archive
	 * @param string $zip_path The path to the existing archive
	 * @return Synthesis_Zip
	 */
	public static function open( $zip_path ) {
		return new Synthesis_Zip( $zip_path, false );
	}

	/**
	 * Close (write) the zip archive
	 */
	public function close() {
		$this->zip_archive->close();
	}

	/**
	 */

	/**
	 * Recursively add the contents of a directory to a folder within the zip directory
	 * @param string $directory The directory to add to the zip archive
	 * @param string $zip_folder The folder inside the zip archive where the directory contents should be added
	 * @param array|null $exclude_paths Paths to exclude. Passing null will use the default exclusions
	 * @param array $included_paths Paths to explicitly include. Overrides $excluded_paths
	 * @param int $max_depth How deep to traverse into the directory
	 */
	public function zip_dir( $directory, $zip_folder, $exclude_paths = null, $included_paths = array(), $max_depth = 100 ) {
		$directory = untrailingslashit( str_replace( '\\', '/', realpath( $directory ) ) );
		$zip_folder = untrailingslashit( str_replace( '\\', '/', $zip_folder ) );
		$this->zip_dir_recursive( $directory, $zip_folder, $exclude_paths, $included_paths, $max_depth );
	}

	/**
	 * Add a single file to the zip archive
	 * @param string $file_path The path of the file to add
	 * @param string $zip_file_path The target path within the archive (including file name)
	 */
	public function zip_file( $file_path, $zip_file_path ) {
		$this->zip_archive->addFile( $file_path, $zip_file_path );
	}

	/**
	 * Recursively add the contents of a directory to a folder within the zip directory
	 * @param string $directory The directory to add to the zip archive
	 * @param string $zip_folder The folder inside the zip archive where the directory contents should be added
	 * @param array|null $exclude_paths Paths to exclude. Passing null will use the default exclusions
	 * @param array $included_paths Paths to explicitly include. Overrides $excluded_paths
	 * @param int $max_depth How deep to traverse into the directory
	 */
	private function zip_dir_recursive( $directory, $zip_folder, $exclude_paths = null, $included_paths = array(), $max_depth = 100 ) {
		if ( $max_depth == 0 ) {
			return;
		}

		if ( is_null( $exclude_paths ) ) {
			$exclude_paths = array();
		}

		$exclude_paths = array_merge( self::get_default_exclude_paths(), $exclude_paths );

		$it = new DirectoryIterator( $directory );

		foreach ( $it as $file ) {
			/** @var DirectoryIterator $file */
			if ( !$file->isDot() ) {
				$file_name = $file->getFilename();
				if ( $file->isDir() ) {
					$this->zip_dir_recursive( $file->getPathname(), trailingslashit( $zip_folder ) . $file, $exclude_paths, $included_paths, $max_depth-1 );
				} elseif ( $file->isFile() && $file_name !== realpath( $this->zip_path ) ) {
					if ( !self::is_excluded_path( $exclude_paths, $included_paths, $file ) ) {
						do_action( 'synthesis_clone_line', 'Zipping file ' . $file->getPathname() );
						$this->zip_file( $file->getPathname(), trailingslashit( $zip_folder ) . self::rename_file( $file_name, $file->getPathname() ) );
					}
				}
			}
		}

		@closedir($it);
	}

	/**
	 * Extract the entire zip archive to a given destination
	 * @param string $destination Where the zip archive should be extracted to
	 */
	public function extract( $destination ) {
		self::extract_dir( '', $destination );
	}

	/**
	 * Extract a directory from the zip archive into a destination directory
	 * @param string $zip_dir The directory within the zip file to extract
	 * @param string $destination The destination to extract the zip folder to
	 */
	public function extract_dir( $zip_dir, $destination ) {
		$files = array();
		for ( $i = 0; $i < $this->zip_archive->numFiles; $i++ ) {
			$entry = $this->zip_archive->getNameIndex($i);

			if ( 0 === strpos( $entry, $zip_dir ) ) {

				do_action( 'synthesis_clone_line', "Extracting file $entry" );

				$destination_path = trailingslashit( $destination ) . preg_replace( '/' . preg_quote( trailingslashit( $zip_dir ), '/' ) . '/', '', $entry );
				$destination_folder = dirname( $destination_path );

				if ( !file_exists( $destination_folder ) ) {
					mkdir( $destination_folder, 0755, true );
				}
				copy( 'zip://' . $this->zip_path . '#' . $entry, $destination_path );
			}
		}
	}

	/**
	 * Returns a list of paths to exclude from zipping
	 * @return array The list of excluded paths
	 */
	public static function get_default_exclude_paths() {
		return apply_filters( 'synthesis_zip_default_exclude_paths', array(
			'/.git',
			'/.idea',
			'/wp-content-pre.php', // Don't back up an old "replaced" config
			'/cache/page',
			'/cache/page_enhanced',
			'/cache/db',
			'/cache/object',
			'/wp-content/w3tc/',
			'/wp-content/uploads/backupbuddy_backups',
			'/wp-content/uploads/backupbuddy_temp',
			'/wp-content/updraft',
			'/wp-content/managewp/backups',
			'/wp-content/uploads/wp-clone',
			'/wp-content/cache'
		) );
	}

	/**
	 * Get the new name for a given file if it needs to be renamed
	 *
	 * This function currently exists to rename wp-config.php to wp-config-pre.php
	 * @param string $file_name The name of the file to rename
	 * @param string $full_path The full path of the file to rename
	 * @return string The renamed file, or $file_name if the file doesn't need to be renamed
	 */
	public static function rename_file( $file_name, $full_path = '' ) {
		// TODO: Figure out support for moving files between folders
		$files_to_rename = array(
			ABSPATH . 'wp-config.php' => 'wp-config-pre.php'
		);

		foreach ( $files_to_rename as $file => $new_name ) {
			if ( $file === $full_path ) {
				return $new_name;
			}
		}

		return $file_name;
	}

	/**
	 * @param array $excluded_paths The paths and regex expressions to exclude
	 * @param array $included_paths The paths to include. Overrides $excluded_paths
	 * @param DirectoryIterator $file The object containing extra data about the file to check
	 * @return bool
	 */
	private static function is_excluded_path( $excluded_paths, $included_paths, $file ) {
		foreach ( $included_paths as $included_path ) {
			if ( !empty( $included_path ) ) {
				if ( self::path_matches( $included_path, $file->getPathname() ) ) {
					return false;
				}
			}
		}

		foreach ( $excluded_paths as $excluded_path ) {
			if ( !empty( $excluded_path ) ) {
				if ( self::path_matches( $excluded_path, $file->getPathname() ) ) {
					return true;
				}
			}
		}

		// Check whether a filter excludes this path
		if ( apply_filters( 'synthesis_zip_exclude_path', false, $file ) ) {
			return true;
		}

		// Check whether this is a directory and a filter excludes this directory
		if ( $file->isDir() && apply_filters( 'synthesis_zip_exclude_dir', false, $file ) ) {
			return true;
		}

		// Check whether this is a file and a filter excludes this file
		if ( $file->isFile() && apply_filters( 'synthesis_zip_exclude_file', false, $file ) ) {
			return true;
		}

		return false;
	}

	private static function path_matches( $match, $path ) {
		if ( '#' == $match[0] && '#' == $match[ strlen( $match ) - 1 ] ) {
			if ( preg_match( $match, $path ) ) {
				return true;
			}
		} else if ( false !== strpos( $path, $match ) ) {
			return true;
		}

		return false;
	}
}