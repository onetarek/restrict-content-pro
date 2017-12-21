<?php
namespace RCP\Utils;

class BatchFunctions extends \WP_UnitTestCase {

	protected $config;

	protected $invalidConfig;

	protected $job;

	public function setUp() {

		$this->config = array(
			'name' => 'Test Job',
			'description' => 'Test Job Description',
			'callback' => '\RCP\Utils\batch_callback_test'
		);

		$this->invalidConfig = array( 'moo' => 'cow' );

		parent::setUp();
	}

	public function tearDown() {
		$this->config = array();
		$this->invalidConfig = array();
	}

	/** @covers \RCP\Utils\rcp_add_batch_job() */
	public function test_add_batch_job_returns_true() {
		$this->assertTrue( rcp_add_batch_job( $this->config ) );
	}

	/** @covers \RCP\Utils\rcp_add_batch_job() */
	public function test_add_batch_job_invalid_config_returns_WP_Error() {
		$this->assertInstanceOf( 'WP_Error', rcp_add_batch_job( $this->invalidConfig ) );
	}

	/** @covers \RCP\Utils\rcp_delete_batch_job() */
	public function test_delete_batch_job_returns_true() {
		rcp_add_batch_job( $this->config );
		$this->assertTrue( rcp_delete_batch_job( $this->config['name'] ) );
	}

	/** @covers \RCP\Utils\rcp_delete_batch_job() */
	public function test_delete_batch_job_returns_false_with_invalid_job_name() {
		$this->assertFalse( rcp_delete_batch_job( 'RCP this job name is fake' ) );
	}

	/** @covers \RCP\Utils\Job() */
	public function test_job_throws_InvalidArgumentException_with_invalid_job_name() {
		$this->expectException( '\InvalidArgumentException' );
		new Job( false );
	}

	/** @covers \RCP\Utils\Job::name() */
	public function test_job_name() {
		rcp_add_batch_job( $this->config );

		$this->job = new Job( 'rcp_' . $this->config['name'] );

		$this->assertSame( 'Test Job', $this->job->name() );
	}

	/** @covers \RCP\Utils\Job::description() */
	public function test_job_description() {
		rcp_add_batch_job( $this->config );

		$this->job = new Job( 'rcp_' . $this->config['name'] );

		$this->assertSame( 'Test Job Description', $this->job->description() );
	}

	/** @covers \RCP\Utils\Job::callback() */
	public function test_job_callback() {
		rcp_add_batch_job( $this->config );

		$this->job = new Job( 'rcp_' . $this->config['name'] );

		$this->assertSame( '\RCP\Utils\batch_callback_test', $this->job->callback() );
	}
}

function batch_callback_test() {
	return true;
}

