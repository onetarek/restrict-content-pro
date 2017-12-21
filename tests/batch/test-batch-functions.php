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

	public function test_add_batch_job() {
		$added = rcp_add_batch_job( $this->config );
		$this->assertTrue( $added );

		$this->job = new Job( $this->config['name'] );

		$invalidJob = rcp_add_batch_job( $this->invalidConfig );
		$this->assertInstanceOf( 'WP_Error', $invalidJob );
	}

	public function test_delete_batch_job() {

		rcp_add_batch_job( $this->config );

		$this->job = new Job( 'rcp_' . $this->config['name'] );

		$deleted = rcp_delete_batch_job( $this->job->name() );

		$this->assertTrue( $deleted );
	}

	public function test_job_properties() {

		rcp_add_batch_job( $this->config );

		$this->job = new Job( 'rcp_' . $this->config['name'] );

		$this->assertSame( 'Test Job', $this->job->name() );

		$this->assertSame( 'Test Job Description', $this->job->description() );

		$this->assertSame( '\RCP\Utils\batch_callback_test', $this->job->callback() );

	}
}

function batch_callback_test() {
	return true;
}

