<?php
/**
 * @author John Parris <public@johnparris.com>
 * @copyright 2017 John Parris
 */
namespace RCP\Utils;
use \InvalidArgumentException;

/**
 * Class Job
 * @package RCP\Utils
 * @since 3.0
 */
final class Job implements JobInterface {

	/**
	 * @var string The key/name used to retrieve the requested job.
	 */
	private $key;

	/**
	 * @var array The job config
	 */
	private $config;

	/**
	 * Job constructor.
	 *
	 * @param string $name The job name
	 * @throws InvalidArgumentException when an invalid job name is supplied.
	 */
	public function __construct( $name ) {
		if ( empty( $name ) || ! is_string( $name ) ) {
			throw new InvalidArgumentException( __( 'You must supply a valid job name to use Job.', 'rcp' ) );
		}

		$this->key = 'rcp_job_' . sanitize_key( $name );

		$this->config = get_option(
			$this->key,
			array(
				'name' => '',
				'description' => '',
				'callback' => ''
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function name() {
		return $this->config['name'];
	}

	/**
	 * @inheritdoc
	 */
	public function description() {
		return $this->config['description'];
	}

	/**
	 * @inheritdoc
	 */
	public function callback() {
		return $this->config['callback'];
	}

	/**
	 * @inheritdoc
	 */
	public function percent_complete() {
		$total_count = $this->total_count();
		if ( empty( $total_count ) ) {
			return 100;
		}
		return (int) ( ( $this->current_count() / $total_count ) * 100 );
	}

	/**
	 * @inheritdoc
	 */
	public function status() {
		return ! empty( $this->config['status'] ) ? $this->config['status'] : 'incomplete';
	}

	/**
	 * @inheritdoc
	 */
	public function finish() {
		$this->config['status'] = 'complete';
		$this->save();
	}

	/**
	 * @inheritdoc
	 */
	public function completed() {
		return ( ( $this->percent_complete() >= 100 ) || ( 'complete' === $this->status() ) );
	}

	/**
	 * @inheritdoc
	 */
	public function current_count() {
		return isset( $this->config['current_count'] ) ? (int) $this->config['current_count'] : 0;
	}

	/**
	 * @inheritdoc
	 */
	public function adjust_current_count( $amount = 0 ) {
		$this->config['current_count'] = $this->current_count() + (int) $amount;
		$this->save();
	}

	/**
	 * @inheritdoc
	 */
	public function total_count() {
		return isset( $this->config['total_count'] ) ? (int) $this->config['total_count'] : 0;
	}

	/**
	 * @inheritdoc
	 */
	public function set_total_count( $count = 0 ) {
		$this->config['total_count'] = absint( $count );
		$this->save();
	}

	/**
	 * @inheritdoc
	 */
	public function save() {
		/** This could easily be swapped out later for a custom table */
		return update_option( $this->key, $this->config, false );
	}
}