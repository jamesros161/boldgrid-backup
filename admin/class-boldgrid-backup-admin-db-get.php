<?php
/**
 * Db Get class.
 *
 * @link  http://www.boldgrid.com
 * @since 1.5.3
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin
 * @copyright  BoldGrid.com
 * @version    $Id$
 * @author     BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Backup Admin Db Get Class.
 *
 * @since 1.5.3
 */
class Boldgrid_Backup_Admin_Db_Get {

	/**
	 * The core class object.
	 *
	 * @since  1.5.3
	 * @access private
	 * @var    Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Constructor.
	 *
	 * @since 1.5.3
	 *
	 * @param Boldgrid_Backup_Admin_Core $core Core class object.
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Get a list of all tables based on system prefix.
	 *
	 * @since 1.5.3
	 *
	 * @global $wpdb;
	 *
	 * @return array
	 */
	public function prefixed() {
		global $wpdb;
		$prefix_tables = array();

		$sql = sprintf( 'SHOW TABLES LIKE "%1$s%%"', $wpdb->prefix );
		$results = $wpdb->get_results( $sql, ARRAY_N );

		foreach( $results as $k => $v ) {
			$prefix_tables[] = $v[0];
		}

		return $prefix_tables;
	}

	/**
	 * Get a list of all prefixed tables and the number of rows in each.
	 *
	 * This is similar to self::prefixed, except this method returns the number
	 * of rows in each table.
	 *
	 * @since 1.5.3
	 *
	 * @return array
	 */
	public function prefixed_count() {
		global $wpdb;
		$return = array();

		$tables = $this->prefixed();

		foreach( $tables as $table ) {
			$sql = sprintf( 'SELECT COUNT(*) FROM %1$s;', $table );
			$num = $wpdb->get_var( $sql );
			$return[$table] = $num;
		}

		return $return;
	}
}