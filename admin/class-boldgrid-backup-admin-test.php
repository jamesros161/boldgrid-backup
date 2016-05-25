<?php
/**
 * The admin-specific test functionality of the plugin
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
 * BoldGrid Backup admin test class.
 *
 * @since 1.0
 */
class Boldgrid_Backup_Admin_Test {
	/**
	 * The core class object.
	 *
	 * @since 1.0
	 * @access private
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Is running Windows?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $is_windows = null;

	/**
	 * Is the WordPress installation root directory (ABSPATH) writable?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $is_abspath_writable = null;

	/**
	 * Is crontab available?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $is_crontab_available = null;

	/**
	 * Is WP-CRON enabled?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $wp_cron_enabled = null;

	/**
	 * Is mysqldump available?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $mysqldump_available = null;

	/**
	 * Is PHP in safe mode?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $is_php_safemode = null;

	/**
	 * Functionality tests completed?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $functionality_tested = false;

	/**
	 * Is functional?
	 *
	 * @since 1.0
	 * @access private
	 * @var bool
	 */
	private $is_functional = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param Boldgrid_Backup_Admin_Config $core Config class object.
	 */
	public function __construct( $core ) {
		// Save the Boldgrid_Backup_Admin_Core object as a class property.
		$this->core = $core;
	}

	/**
	 * Check if using Windows.
	 *
	 * @since 1.0
	 *
	 * @return bool TRUE is using Windows.
	 */
	public function is_windows() {
		// If was already checked, then return result from the class property.
		if ( null !== $this->is_windows ) {
			return $this->is_windows;
		}

		// Check if using Windows or Linux, and set as a class property.
		$this->is_windows = ( 'win' === strtolower( substr( PHP_OS, 0, 3 ) ) );

		// Return result.
		return $this->is_windows;
	}

	/**
	 * Is crontab available?
	 *
	 * Once the success is determined, the result is stored in a class property.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function is_crontab_available() {
		// If this test was already completed, then just return the result.
		if ( null !== $this->is_crontab_available ) {
			return $this->is_crontab_available;
		}

		// Create the test command.
		$command = 'crontab -l';

		// Test to see if the crontab command is available.
		$output = $this->core->execute_command( $command, null, $success );

		// Set class property.
		$this->is_crontab_available = ( $success || (bool) $output );

		return $this->is_crontab_available;
	}

	/**
	 * Is WP-CRON enabled?
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function wp_cron_enabled() {
		// If this test was already completed, then just return the result.
		if ( null !== $this->wp_cron_enabled ) {
			return $this->wp_cron_enabled;
		}

		// Get the WP-CRON array.
		$wp_cron_array = array();

		if ( true === function_exists( '_get_cron_array' ) ) {
			$wp_cron_array = _get_cron_array();
		}

		// Check for the DISABLE_WP_CRON constant and value.
		$disable_wp_cron = false;

		if ( true === defined( 'DISABLE_WP_CRON' ) ) {
			$disable_wp_cron = DISABLE_WP_CRON;
		}

		$this->wp_cron_enabled = ( false === empty( $wp_cron_array ) && false === $disable_wp_cron );

		return $this->wp_cron_enabled;
	}

	/**
	 * Is mysqldump available?
	 *
	 * Once the success is determined, the result is stored in a class property.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function is_mysqldump_available() {
		// If this test was already completed, then just return the result.
		if ( null !== $this->mysqldump_available ) {
			return $this->mysqldump_available;
		}

		// Create the test command.
		$command = 'mysqldump -V';

		// Test to see if the mysqldump command is available.
		$output = $this->core->execute_command( $command, null, $success );

		// Set class property.
		$this->mysqldump_available = ( $success || (bool) $output );

		return $this->mysqldump_available;
	}

	/**
	 * Is PHP running in safe mode?
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function is_php_safemode() {
		// If this test was already completed, then just return the result.
		if ( null !== $this->is_php_safemode ) {
			return $this->is_php_safemode;
		}

		// Check if PHP is in safe mode.
		$this->is_php_safemode = (bool) ini_get( 'safe_mode' );

		// Return result.
		return $this->is_php_safemode;
	}

	/**
	 * Perform functionality tests.
	 *
	 * @since 1.0
	 *
	 * @global WP_Filesystem $wp_filesystem The WordPress Filesystem API global object.
	 *
	 * @return bool
	 */
	public function run_functionality_tests() {
		// If functionality tests were already performed, then just return status.
		if ( true === $this->functionality_tested && null !== $this->is_functional ) {
			return $this->is_functional;
		}

		// Connect to the WordPress Filesystem API.
		global $wp_filesystem;

		// If not writable, then mark as not functional.
		if ( true !== $this->get_is_abspath_writable() ) {
			$this->is_functional = false;
		}

		// Configure the backup directory path, or mark as not functional.
		if ( false === $this->core->config->get_backup_directory() ) {
			$this->is_functional = false;
		}

		// Get available compressors.
		$available_compressors = $this->core->config->get_available_compressors();

		// Test for available compressors, and add them to the array, or mark as not functional.
		if ( true === empty( $available_compressors ) ) {
			$this->is_functional = false;
		}

		// Test for crontab. For now, don't check if wp-cron is enabled.
		if ( true !== $this->is_crontab_available() ) {
			$this->is_functional = false;
		}

		// Test for mysqldump. For now, don't use wpbd.
		if ( true !== $this->is_mysqldump_available() ) {
			$this->is_functional = false;
		}

		// Test for PHP safe mode.
		if ( false !== $this->is_php_safemode() ) {
			$this->is_functional = false;
		}

		// Test for PHP Zip (currently the only one coded).
		if ( true !== $this->core->config->is_compressor_available( 'php_zip' ) ) {
			$this->is_functional = false;
		}

		// Save result, if not previously saved.
		if ( null === $this->is_functional ) {
			$this->is_functional = true;
		}

		// Mark as completed.
		$this->functionality_tested = true;

		return $this->is_functional;
	}

	/**
	 * Disk space report.
	 *
	 * @since 1.0
	 *
	 * @global WP_Filesystem $wp_filesystem The WordPress Filesystem API global object.
	 *
	 * @param bool $get_wp_size Whether of not to include the size of the WordPress directory.
	 * @return array An array containing disk space (total, used, available, WordPress directory).
	 */
	public function get_disk_space( $get_wp_size = true ) {
		// Connect to the WordPress Filesystem API.
		global $wp_filesystem;

		// Get the home directory.
		$home_dir = $this->core->config->get_home_directory();

		// If the home directory is not defined, not a directory or not writable, then return 0.00.
		if ( true === empty( $home_dir ) || false === $wp_filesystem->is_dir( $home_dir ) ||
			false === $wp_filesystem->is_writable( $home_dir ) ) {
			return array(
				0.00,
				0.00,
				0.00,
			);
		}

			// Get filesystem disk space information.
			$disk_total_space = disk_total_space( $home_dir );
			$disk_free_space = disk_free_space( $home_dir );
			$disk_used_space = $disk_total_space - $disk_free_space;

			// Initialize $wp_root_size.
			$wp_root_size = false;

			// Get the size of the filtered WordPress installation root directory (ABSPATH).
			if ( true === $get_wp_size ) {
				$wp_root_size = $this->get_wp_size();
			}

			// Return the disk information array.
			return array(
				$disk_total_space,
				$disk_used_space,
				$disk_free_space,
				$wp_root_size,
			);
	}

	/**
	 * Get the WordPress total file size.
	 *
	 * @since 1.0
	 * @access private
	 *
	 * @see get_filtered_filelist
	 *
	 * @return int|bool The total size for the WordPress file system in bytes, or FALSE on error.
	 */
	private function get_wp_size() {
		// Perform functionality tests.
		$is_functional = $this->run_functionality_tests();

		// If plugin is not functional, then return FALSE.
		if ( false === $is_functional ) {
			return false;
		}

		// Get the filtered file list.
		$filelist = $this->core->get_filtered_filelist( ABSPATH );

		// If nothing was found, then return 0.
		if ( true === empty( $filelist ) ) {
			return 0;
		}

		// Initialize total_size.
		$size = 0;

		// Add up the file sizes.
		foreach ( $filelist as $fileinfo ) {
			// Add the file size to the total.
			// get_filelist() returns fileinfo array with index 2 for filesize.
			$size += $fileinfo[2];
		}

		// Return the result.
		return $size;

	}

	/**
	 * Get database size.
	 *
	 * @since 1.0
	 *
	 * @global wpdb $wpdb The WordPress database class object.
	 *
	 * @return int The total size of the database (in bytes).
	 */
	public function get_database_size() {
		// Connect to the WordPress database via $wpdb.
		global $wpdb;

		// Build query.
		$query = $wpdb->prepare(
			'SELECT SUM(`data_length` + `index_length`) FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`=%s AND `TABLE_NAME` LIKE %s GROUP BY `TABLE_SCHEMA`;',
			DB_NAME, $wpdb->get_blog_prefix( is_multisite() ) . '%'
		);

		// Check query.
		if ( true === empty( $query ) ) {
			return 0;
		}

		// Get the result.
		$result = $wpdb->get_row( $query, ARRAY_N );

		// If there was an error or nothing returned, then fail.
		if ( empty( $result ) ) {
			return 0;
		}

		// Return result.
		return $result[0];
	}

	/**
	 * Get and return a boolean for whether or not the ABSPATH is writable.
	 *
	 * @since 1.0
	 *
	 * @global WP_Filesystem $wp_filesystem The WordPress Filesystem API global object.
	 *
	 * @return bool
	 */
	public function get_is_abspath_writable() {
		if ( null !== $this->is_abspath_writable ) {
			return $this->is_abspath_writable;
		}

		// Connect to the WordPress Filesystem API.
		global $wp_filesystem;

		// Determine if ABSPATH is writable.
		$this->is_abspath_writable = $wp_filesystem->is_writable( ABSPATH );

		// Return the result.
		return $this->is_abspath_writable;
	}

	/**
	 * Get and return a boolean for whether or not the plugin will be functional.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function get_is_functional() {
		// If functionality tests were already performed, then just return status.
		if ( true === $this->functionality_tested && null !== $this->is_functional ) {
			return $this->is_functional;
		}

		// Run the functionality tests.
		$this->run_functionality_tests();

		// Return the result.
		return $this->is_functional;
	}
}