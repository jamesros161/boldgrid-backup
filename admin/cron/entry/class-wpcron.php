<?php
/**
 * WPCron Entry class.
 *
 * This class represents a single entry within WPCron.
 *
 * @link       https://www.boldgrid.com
 * @since      xxx
 *
 * @package    Boldgrid\Backup
 * @subpackage Boldgrid\Backup\Cron
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Backup\Admin\Cron\Entry;

use Boldgrid\Backup\Admin\Cron\Entry\Entry;
use Boldgrid\Backup\Admin\Cron\Entry\Base;

/**
 * Class: Entry
 *
 * @since xxx
 */
class Wpcron extends Base implements Entry {
	/**
	 * The hook this wpcron uses.
	 *
	 * @since xxx
	 * @access private
	 * @var string
	 */
	private $hook;

	/**
	 * Whether or not this wpcron is set / configured within the wpcron system.
	 *
	 * @since xxx
	 * @access private
	 * @var bool
	 */
	private $is_set;

	/**
	 * Get the next runtime of this wpcron.
	 *
	 * @since xxx
	 *
	 * @return string Timestamp, the time the scheduled event will next occur (unix timestamp).
	 *                False, if the event isn't scheduled.
	 */
	public function get_next_runtime() {
		return wp_next_scheduled( $this->hook );
	}

	/**
	 * Initialize this wpcron entry.
	 *
	 * @since xxx
	 *
	 * @param string $hook
	 */
	public function init_via_search( array $patterns = [] ) {
		$this->is_set = false;

		$this->hook = $patterns[0];

		$schedule = wp_get_schedule( $this->hook );

		if ( ! empty( $schedule ) ) {
			$this->is_set = true;
		}
	}

	/**
	 * Get whether or not this wpcron entry exists.
	 *
	 * @since xxx
	 *
	 * @return bool
	 */
	public function is_set() {
		return $this->is_set;
	}
}
