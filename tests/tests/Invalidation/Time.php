<?php

namespace Cache\Invalidation\Tests;

class Time extends \WP_UnitTestCase {

	public $plugins = array( 'cache/bootstrap.php' );
	/**
	 * @var \Cache\Engine\Mock
	 */
	public $engine = null;
	/**
	 * @var \Cache\Engine\Cache
	 */
	public $cache = null;
	/**
	 * @var \Cache\Content\Base
	 */
	public $base     = null;
	public $key      = 'testing_cache_key';
	public $callable = array( __CLASS__, 'callable_test' );

	static $time = null;

	public function setUp() {
		parent::setUp();

		if ( ! function_exists( "activate_plugin" ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $this->plugins as $plugin ) {
			activate_plugin( $plugin );
		}
		$this->engine = new \Cache\Engine\Mock();
		$this->cache  = new \Cache\Engine\Cache( $this->key, $this->callable, array(), $this->engine );
		$this->base   = new \Cache\Content\Base( $this->cache );

		$this->engine->flush();
	}

	public function test_seconds() {

		$invalidation = new \Cache\Invalidation\Time( $this->base );

		$invalidation->in_seconds( 10 );
		$this->base->refresh();

		$this->assertEquals( \Cache\Engine\Mock::$time_expirations[ $this->key ], 10 );
	}

	public function test_minutes() {

		$invalidation = new \Cache\Invalidation\Time( $this->base );

		$invalidation->in_minutes( 10 );
		$this->base->refresh();

		$this->assertEquals( \Cache\Engine\Mock::$time_expirations[ $this->key ], 600 );
	}

	public function test_hours() {

		$invalidation = new \Cache\Invalidation\Time( $this->base );

		$invalidation->in_hours( 10 );
		$this->base->refresh();

		$this->assertEquals( \Cache\Engine\Mock::$time_expirations[ $this->key ], 36000 );
	}

	public function test_days() {

		$invalidation = new \Cache\Invalidation\Time( $this->base );

		$invalidation->in_days( 10 );
		$this->base->refresh();

		$this->assertEquals(\Cache\Engine\Mock::$time_expirations[ $this->key ], 864000 );
	}

	/**
	 * Sample callable to hook into cache
	 */
	public static function callable_test() {
		self::$time = "Time = " . rand();

		echo self::$time;
	}

}


