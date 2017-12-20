<?php
/**
 * @author John Parris <public@johnparris.com>
 * @copyright 2017 John Parris
 */
namespace RCP\Utils;

/**
 * Interface JobInterface
 * @package RCP\Utils
 * @since 3.0
 */
interface JobInterface {
	/**
	 * Returns the job name.
	 *
	 * @return string
	 */
	public function name();

	/**
	 * Returns the job description.
	 *
	 * @return string
	 */
	public function description();

	/**
	 * Returns the callback.
	 *
	 * @return mixed
	 */
	public function callback();

	/**
	 * Returns the job completion percentage.
	 *
	 * @return int
	 */
	public function percent_complete();

	/**
	 * Returns the job status.
	 *
	 * @return string
	 */
	public function status();

	/**
	 * Runs any tasks required to finish a job.
	 *
	 * @return mixed|void
	 */
	public function finish();

	/**
	 * Returns the job completed status.
	 *
	 * @return boolean True if the job is completed, false if not.
	 */
	public function completed();

	/**
	 * Returns the number of records already processed.
	 *
	 * @return int
	 */
	public function current_count();

	/**
	 * Adjusts the number of records already processed.
	 *
	 * @param  int $amount Amount to add to or subtract from the current count.
	 * @return mixed|void
	 */
	public function adjust_current_count( $amount = 0 );

	/**
	 * Returns the total number of records to be processed.
	 *
	 * @return int
	 */
	public function total_count();

	/**
	 * Sets the number of total records to be processed.
	 *
	 * @param int $count
	 * @return mixed|void
	 */
	public function set_total_count( $count = 0 );

	/**
	 * Saves the job's current state.
	 *
	 * @return bool True if saved, false if not.
	 */
	public function save();
}