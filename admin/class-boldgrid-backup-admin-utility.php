<?php
/**
 * The admin-specific utility methods for the plugin
 *
 * @link http://www.boldgrid.com
 * @since 1.0
 *
 * @package Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Backup admin utility class.
 *
 * @since 1.0
 */
class Boldgrid_Backup_Admin_Utility {
	/**
	 * Convert bytes to a human-readable measure.
	 *
	 * @since 1.0
	 *
	 * @static
	 *
	 * @param int $bytes Number of bytes.
	 * @param int $decimals Number of decimal places.
	 * @return string
	 */
	public static function bytes_to_human( $bytes = 0, $decimals = 2 ) {
		// If $bytes is not a number, then fail.
		if ( ! is_numeric( $bytes ) ) {
			return 'INVALID';
		}

		// Ensure the $decimals is an integer.
		$decimals = (int) $decimals;

		$type = array(
			'B',
			'KB',
			'MB',
			'GB',
			'TB',
			'PB',
			'EB',
			'ZB',
			'YB',
		);

		$index = 0;

		while ( $bytes >= 1024 ) {
			$bytes /= 1024;
			$index ++;
		}

		$return = number_format( $bytes, $decimals, '.', '' ) . ' ' . $type[ $index ];

		return $return;
	}

	/**
	 * Create a site identifier.
	 *
	 * @since 1.0
	 *
	 * @static
	 *
	 * @return string The site identifier.
	 */
	public static function create_site_id() {
		// Get the siteurl.
		if ( is_multisite() ) {
			// Use the siteurl from blog id 1.
			$siteurl = get_site_url( 1 );
		} else {
			// Get the current siteurl.
			$siteurl = get_site_url();
		}

		// Make an identifier.
		$site_id = explode( '/', $siteurl );
		unset( $site_id[0] );
		unset( $site_id[1] );
		$site_id = implode( '_', $site_id );

		return $site_id;
	}

	/**
	 * Translate a ZipArchive error code into a human-readable message.
	 *
	 * @since 1.0
	 *
	 * @static
	 *
	 * @param int $error_code An error code from a ZipArchive constant.
	 * @return string An error message.
	 */
	public static function translate_zip_error( $error_code = null ) {
		switch ( $error_code ) {
			case ZipArchive::ER_EXISTS :
				$message = esc_html__( 'File already exists', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_INCONS :
				$message = esc_html__( 'Zip archive inconsistent', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_INVAL :
				$message = esc_html__( 'Invalid argument', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_MEMORY :
				$message = esc_html__( 'Malloc failure', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_NOENT :
				$message = esc_html__( 'No such file', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_NOZIP :
				$message = esc_html__( 'Not a zip archive', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_OPEN :
				$message = esc_html__( 'Cannot open file', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_READ :
				$message = esc_html__( 'Read error', 'boldgrid-backup' );
				break;
			case ZipArchive::ER_SEEK :
				$message = esc_html__( 'Seek error', 'boldgrid-backup' );
				break;
			default :
				$message = esc_html__( 'No error code was passed', 'boldgrid-backup' );
				break;
		}

		return $message;
	}

	/**
	 * Translate a file upload error code into a human-readable message.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 *
	 * @param int $error_code An error code from a file upload error constant.
	 * @return string An error message.
	 */
	public static function translate_upload_error( $error_code ) {
		switch ( $error_code ) {
			case UPLOAD_ERR_INI_SIZE:
				$message = esc_html__(
					'The uploaded file exceeds the upload_max_filesize directive in php.ini',
					'boldgrid-backup'
				);
				break;

			case UPLOAD_ERR_FORM_SIZE:
				$message = esc_html__(
					'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
					'boldgrid-backup'
				);
				break;

			case UPLOAD_ERR_PARTIAL:
				$message = esc_html__(
					'The uploaded file was only partially uploaded',
					'boldgrid-backup'
				);
				break;

			case UPLOAD_ERR_NO_FILE:
				$message = esc_html__(
					'No file was uploaded',
					'boldgrid-backup'
				);

				break;

			case UPLOAD_ERR_NO_TMP_DIR:
				$message = esc_html__(
					'Missing a temporary folder',
					'boldgrid-backup'
				);
				break;

			case UPLOAD_ERR_CANT_WRITE:
				$message = esc_html__(
					'Failed to write file to disk',
					'boldgrid-backup'
				);
				break;

			case UPLOAD_ERR_EXTENSION:
				$message = esc_html__(
					'File upload stopped by extension',
					'boldgrid-backup'
				);
				break;

			default:
				$message = esc_html(
					'Unknown upload error',
					'boldgrid-backup'
				);
				break;
		}
		return $message;
	}

	/**
	 * Make a directory or file writable, if exists.
	 *
	 * @since 1.0
	 *
	 * @global WP_Filesystem $wp_filesystem The WordPress Filesystem API global object.
	 *
	 * @static
	 *
	 * @param string $filepath A path to a directory or file.
	 * @return bool Success.
	 */
	public static function make_writable( $filepath ) {
		// Validate file path string.
		$filepath = realpath( $filepath );

		if ( empty( $filepath ) ) {
			return true;
		}

		// Connect to the WordPress Filesystem API.
		global $wp_filesystem;

		// If path exists and is not writable, then make writable.
		if ( $wp_filesystem->exists( $filepath ) ) {
			if ( ! $wp_filesystem->is_writable( $filepath ) ) {
				if ( $wp_filesystem->is_dir( $filepath ) ) {
					// Is a directory.
					if ( ! $wp_filesystem->chmod( $filepath, 0755 ) ) {
						// Error chmod 755 a directory.
						error_log(
							__METHOD__ . ': Error using chmod 0755 on directory "' . $filepath . '".'
						);

						return false;
					}
				} else {
					// Is a file.
					if ( ! $wp_filesystem->chmod( $filepath, 0644 ) ) {
						// Error chmod 644 a file.
						error_log(
							__METHOD__ . ': Error using chmod 0644 on file "' . $filepath . '".'
						);

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Increase the PHP max execution time.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @link http://php.net/manual/en/info.configuration.php#ini.max-execution-time
	 *
	 * @param string $max_execution_time A php.ini style max_execution_time.
	 * @return bool Success of the operation.
	 */
	public static function bump_max_execution( $max_execution_time ) {
		// Abort if in safe mode or max_execution_time is not changable.
		if ( ini_get( 'safe_mode' ) || ! wp_is_ini_value_changeable( 'max_execution_time' ) ) {
			return false;
		}

		// Validate input max_execution_time.
		if ( ! is_numeric( $max_execution_time ) || $max_execution_time < 0 ) {
			return false;
		}

		// Get the current max execution time set for PHP.
		$current_max = ini_get( 'max_execution_time' );

		// If the current max execution time is less than specified, then try to increase it.
		// PHP default is "30".
		if ( $current_max < $max_execution_time ) {
			set_time_limit( $max_execution_time );

			if ( false === ini_set( 'max_execution_time', $max_execution_time ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the file upload limit.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @see wp_convert_hr_to_bytes() in wp-includes/load.php
	 * @link http://php.net/manual/en/ini.core.php#ini.post-max-size
	 * @link http://php.net/manual/en/ini.core.php#ini.upload-max-filesize
	 *
	 * @return int The upload/post limit in bytes.
	 */
	public static function get_upload_limit() {
		// Get PHP setting value for post_max_size.
		// PHP default is "8M".
		$post_max_size = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );

		// Get PHP setting value for upload_max_filesize.
		// PHP default is "2M".
		$upload_max_filesize = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );

		// Determine the minimum value.
		$min = min( $post_max_size, $upload_max_filesize );

		// Return the resulting minimum value (int in bytes).
		return $min;
	}

	/**
	 * Increase the PHP memory limit.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @see wp_is_ini_value_changeable() in wp-includes/default-constants.php
	 * @see wp_convert_hr_to_bytes() in wp-includes/load.php
	 * @link http://php.net/manual/en/ini.core.php#ini.memory-limit
	 *
	 * @param string $memory_limit A php.ini style memory_limit string.
	 * @return bool Success of the operation.
	 */
	public static function bump_memory_limit( $memory_limit ) {
		// Abort if in safe mode or memory_limit is not changable.
		if ( ini_get( 'safe_mode' ) || ! wp_is_ini_value_changeable( 'memory_limit' ) ) {
			return false;
		}

		// Convert memory limit string to an integer in bytes.
		$memory_limit_int = wp_convert_hr_to_bytes( $memory_limit );

		// Get the current upload max filesize set for PHP.
		$current_limit_int = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );

		// Apply a WordPress filter to help ensure the setting.
		apply_filters( 'admin_memory_limit', $memory_limit_int );

		// If the current memory limit is less than specified, then try to increase it.
		// PHP default is "128M".
		if ( $current_limit_int < $memory_limit_int ) {
			if ( false === ini_set( 'memory_limit', $memory_limit_int ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Attempt to increase the PHP max upload size.
	 *
	 * The upload_max_filesize is set as "PHP_INI_PERDIR";
	 * The entry can be set in "php.ini", ".htaccess", "httpd.conf" or ".user.ini".
	 * We can attempt to set it to a higher limit via a filter, as WordPress may have previously
	 * reduced it.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @see wp_convert_hr_to_bytes() in wp-includes/load.php
	 * @link http://php.net/manual/en/ini.sect.safe-mode.php#ini.safe-mode
	 * @link http://php.net/manual/en/ini.core.php#ini.file-uploads
	 * @link http://php.net/manual/en/ini.core.php#ini.max-file-uploads
	 *
	 * @param string $max_filesize A php.ini style upload_max_filesize string.
	 * @return bool Success of the operation.
	 */
	public static function bump_upload_limit( $max_filesize ) {
		// Abort if in safe mode.
		if ( ini_get( 'safe_mode' ) ) {
			return false;
		}

			// Abort if file_uploads is "0" (disabled).
		// PHP default is "1" (enabled).
		if ( ! ini_get( 'file_uploads' ) ) {
			return false;
		}

		// Abort if max_file_uploads is "0" (disabled).
		// PHP default is "20".
		if ( ! ini_get( 'max_file_uploads' ) ) {
			return false;
		}

		// Convert upload max filesize string to an integer in bytes.
		$max_filesize_int = wp_convert_hr_to_bytes( $max_filesize );

		// Apply a WordPress filter to help ensure the setting.
		apply_filters( 'upload_size_limit', $max_filesize_int, $max_filesize_int, $max_filesize_int );

		return true;
	}

	/**
	 * Check if a file is a ZIP archive file.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @see get_filesystem_method() in wp-admin/includes/file.php
	 *
	 * @param string $file A file path to be checked.
	 * @return bool
	 */
	public static function is_zip_file( $file ) {
		// Validate input filename.
		if ( empty( $file ) ) {
			return false;
		}

		// Create a ZipArchive object.
		$zip = new ZipArchive;

		// Check the ZIP file for consistency.
		$status = $zip->open( $file, ZipArchive::CHECKCONS );

		// Close the ZIP file.
		$zip->close();

		// Check the result.
		$result = ( true === $status );

		// Return the result.
		return $result;
	}

	/**
	 * Check if a specific file exists in a ZIP archive.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @link http://php.net/manual/en/class.ziparchive.php
	 *
	 * @param string $zip_file Path to a ZIP file.
	 * @param string $locate_file A filename or path to be located.
	 * @param bool   $is_path Is the input file a path.
	 * @return bool
	 */
	public static function zip_file_exists( $zip_file, $locate_file, $is_path = false ) {
		// Validate input parameters.
		if ( empty( $zip_file ) || empty( $locate_file ) ) {
			return false;
		}

		// Create a ZipArchive object.
		$zip = new ZipArchive;

		// Check the ZIP file for consistency.
		$status = $zip->open( $zip_file, ZipArchive::CHECKCONS );

		if ( true !== $status ) {
			// Invalid ZIP file.
			return false;
		}

		// Locate the filename or path.
		if ( $is_path ) {
			$index = $zip->locateName( $locate_file );
		} else {
			$index = $zip->locateName( $locate_file, ZipArchive::FL_NODIR );
		}

		// Close the ZIP file.
		$zip->close();

		// Return the result.
		return (bool) $index;
	}

	/**
	 * Check if specific entry patterns exists in a ZIP archive.
	 *
	 * @since 1.2.2
	 *
	 * @static
	 *
	 * @link http://php.net/manual/en/class.ziparchive.php
	 *
	 * @param string $zip_file Path to a ZIP file.
	 * @param array  $locate_files An array of filenames and/or paths to be located.
	 * @return bool
	 */
	public static function zip_patterns_exist( $zip_file, $locate_files ) {
		// Validate input parameters.
		if ( empty( $zip_file ) || empty( $locate_files ) || ! is_array( $locate_files ) ) {
			return false;
		}

		// Create a ZipArchive object.
		$zip = new ZipArchive;

		// Check the ZIP file for consistency.
		$status = $zip->open( $zip_file, ZipArchive::CHECKCONS );

		if ( true !== $status ) {
			// Invalid ZIP file.
			return false;
		}

		// Define an array of paterns to skip.
		$skip = array(
			'.htaccess',
		);

		// Check for each string pattern in the $locate_files array.
		// This is a loose search, so we can also search for directory names.
		foreach ( $locate_files as $locate_entry ) {
			// Skip certain patterns.
			if ( in_array( $locate_entry, $skip, true ) ) {
				continue;
			}

			// Initialize $found.
			$found = false;

			// Iterate through the ZIP file list.
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				// Get the list entry name.
				$entry = $zip->getNameIndex( $i );

				if ( false !== strpos( $entry, $locate_entry ) ) {
					// Pattern was found; skip to the next iteration.
					$found = true;

					break;
				}
			}

			if ( ! $found ) {
				return false;
			}
		}

		// Close the ZIP file.
		$zip->close();

		// Return success.
		return true;
	}

	/**
	 * Chmod a directory or file.
	 *
	 * @since 1.2.2
	 *
	 * @global WP_Filesystem $wp_filesystem The WordPress Filesystem API global object.
	 *
	 * @param string $file Path to a directory or file.
	 * @param int    $mode 	(Optional) The permissions as octal number, usually 0644 for files, 0755 for dirs.
	 * @return bool
	 */
	public static function chmod( $file, $mode = false ) {
		// Connect to the WordPress Filesystem API.
		global $wp_filesystem;

		// Modify the file permissions.
		$result = $wp_filesystem->chmod( $file, $mode );

		// Return the result.
		return $result;
	}

	/**
	 * Fix wp-config.php file.
	 *
	 * If restoring "wp-config.php", then ensure that the credentials remain intact.
	 *
	 * @since 1.2.2
	 *
	 * @see http://us1.php.net/manual/en/function.preg-replace.php#103985
	 * @global WP_Filesystem $wp_filesystem The WordPress Filesystem API global object.
	 *
	 * @static
	 *
	 * @return bool
	 */
	public static function fix_wpconfig() {
		// Connect to the WordPress Filesystem API.
		global $wp_filesystem;

		// Set the file path.
		$file = ABSPATH . 'wp-config.php';

		// Abort if the file does not exist.
		if ( ! $wp_filesystem->exists( $file ) ) {
			return false;
		}

		// Get the file contents.
		$file_contents = $wp_filesystem->get_contents( $file );

		// Create an array containing the definition names to replace.
		$definitions = array(
			'DB_NAME',
			'DB_USER',
			'DB_PASSWORD',
			'DB_HOST',
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		// Replace the definitions.
		foreach ( $definitions as $definition ) {
			// If the definition does not exist, then skip it.
			if ( ! defined( $definition ) ) {
				continue;
			}

			// Replace $n ($0-$99) backreferences before preg_replace.
			// @see http://us1.php.net/manual/en/function.preg-replace.php#103985 .
			$value = preg_replace( '/\$(\d)/', '\\\$$1', constant( $definition ) );

			// Replace definition.
			$file_contents = preg_replace(
				'#define.*?' . $definition . '.*;#',
				"define('" . $definition . "', '" . $value . "');",
				$file_contents,
				1
			);

			// If there was a failure, then abort.
			if ( null === $file_contents ) {
				return false;
			}
		}

		// Write the changes to file.
		$wp_filesystem->put_contents( $file, $file_contents, 0600 );

		return true;
	}

	/**
	 * Replace the siteurl in the WordPress database.
	 *
	 * @since 1.2.3
	 *
	 * @see Boldgrid_Backup_Admin_Utility::str_replace_recursive()
	 * @global wpdb $wpdb The WordPress database class object.
	 *
	 * @static
	 *
	 * @param string $old_siteurl The old/restored siteurl to find and be replaced.
	 * @param string $new_siteurl The siteurl to replace the old siteurl.
	 * @return bool
	 */
	public static function update_siteurl( $old_siteurl, $new_siteurl ) {
		// Define filter options.
		$filter_options = FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED;

		// Validate the old siteurl.
		if ( false === filter_var( $old_siteurl, FILTER_VALIDATE_URL, $filter_options ) ) {
			return false;
		}

		// Validate the new siteurl.
		if ( false === filter_var( $new_siteurl, FILTER_VALIDATE_URL, $filter_options ) ) {
			return false;
		}

		// Ensure there are no trailing slashes in siteurl.
		$old_siteurl = untrailingslashit( $old_siteurl );
		$new_siteurl = untrailingslashit( $new_siteurl );

		// Update the WP otion "siteurl".
		update_option( 'siteurl', $new_siteurl );

		// Connect to the WordPress database via $wpdb.
		global $wpdb;

		// Get the database prefix (blog id 1 or 0 gets the base prefix).
		$db_prefix = $wpdb->get_blog_prefix( 1 );

		// Replace the URL in wp_posts.
		$wpdb->query(
			$wpdb->prepare(
				'UPDATE `%1$sposts` SET `post_content` = REPLACE( `post_content`, %2$s, %3$s ) WHERE `post_content` LIKE \'%%%2$s%%\';',
				array(
					$db_prefix,
					$old_siteurl,
					$new_siteurl,
				)
			)
		);

		// Check if the upload_url_path needs to be updated.
		$upload_url_path = get_option( 'upload_url_path' );

		if ( ! empty( $upload_url_path ) ) {
			$upload_url_path = str_replace( $old_siteurl, $new_siteurl, $upload_url_path );

			update_option( 'upload_url_path', $upload_url_path );
		}

		// Find old siteurl references in WP options.
		$matched_options = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT `option_name` FROM `%1$soptions` WHERE `option_value` LIKE \'%%%2$s%%\' OR `option_value` LIKE \'%%%3$s%%\';',
				array(
					$db_prefix,
					$old_siteurl,
					addslashes( $old_siteurl ),
				)
			),
			ARRAY_N
		);

		// If there are no matches options, then return.
		if ( ! $matched_options ) {
			return true;
		}

		// Replace the siteurl in matched options.
		foreach ( $matched_options as $option_name ) {
			$option_value = get_option( $option_name[0] );

			// Replace siteurl.
			$option_value = Boldgrid_Backup_Admin_Utility::str_replace_recursive(
				$old_siteurl,
				$new_siteurl,
				$option_value
			);

			// Replace siteurl escaped with slashes.
			$option_value = Boldgrid_Backup_Admin_Utility::str_replace_recursive(
				addslashes( $old_siteurl ),
				addslashes( $new_siteurl ),
				$option_value
			);

			update_option( $option_name[0], $option_value );
		}

		return true;
	}

	/**
	 * Replace string(s) in a string or recurively in an array or object.
	 *
	 * @since 1.2.3
	 *
	 * @static
	 *
	 * @param string $search Search string.
	 * @param string $replace Replace string.
	 * @param mixed  $subject Input subject (array|object|string).
	 * @return mixed The input subject with recursive string replacements.
	 */
	public static function str_replace_recursive( $search, $replace, $subject ) {
		if ( is_string( $subject ) ) {
			$subject = str_replace( $search, $replace, $subject );
		} elseif ( is_array( $subject ) ) {
			foreach ( $subject as $index => $element ) {
				// Recurse.
				$subject[ $index ] = Boldgrid_Backup_Admin_Utility::str_replace_recursive(
					$search,
					$replace,
					$element
				);
			}
		} elseif ( is_object( $subject ) ) {
			foreach ( $subject as $index => $element ) {
				// Recurse.
				$subject->$index = Boldgrid_Backup_Admin_Utility::str_replace_recursive(
					$search,
					$replace,
					$element
				);
			}
		}

		return $subject;
	}
}
