<?php

namespace Cache\Engine\Tests;


class Cache extends \WP_UnitTestCase {

	public $plugins = array( 'cache/bootstrap.php' );
	/**
	 * @var \Cache\Engine\Mock
	 */
	public $engine   = null;
	public $callable = array( __CLASS__, 'callable_test' );
	static $value    = null;

	public function setUp() {
		parent::setUp();

		if ( ! function_exists( "activate_plugin" ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $this->plugins as $plugin ) {
			activate_plugin( $plugin );
		}

		$this->engine = new \Cache\Engine\Mock();

		global $wp_filter;
		$wp_filter = array();

		$this->engine->flush();

	}

	public function test_callback() {
		$cache = new \Cache\Engine\Cache( 'some_key', $this->callable, array(), $this->engine );
		$value = $cache->get();


		$this->assertEquals( $value, self::$value );
	}

	public function test_callback_with_argument() {
		$cache = new \Cache\Engine\Cache( 'some_key', $this->callable, array( 'arg' => 'Hello!' ), $this->engine );
		$value = $cache->get();


		$this->assertEquals( $value, 'Hello!' );
	}

	public function test_cache() {
		$key    = 'some_key';
		$cache  = new \Cache\Engine\Cache( $key, $this->callable, array(), $this->engine );
		$value1 = $cache->get();
		$value2 = $cache->get();

		$this->assertEquals( $value1, $value2 );
		$this->assertEquals( $value1, $this->engine->get( $key ) );

	}

	public function test_refresh() {
		$key    = 'some_key';
		$cache  = new \Cache\Engine\Cache( $key, $this->callable, array(), $this->engine );
		$value1 = $cache->get();
		$value2 = $cache->refresh();

		$this->assertNotEquals( $value1, $value2 );
	}

	public function test_change_key() {
		$key  = 'some_key';
		$key2 = 'some_other_key';
		$this->engine->flush();
		$cache  = new \Cache\Engine\Cache( $key, $this->callable, array(), $this->engine );
		$value1 = $cache->get();
		$cache->key( $key2 );
		$value2 = $cache->get();

		$this->assertNotEquals( $value1, $value2 );
		$this->assertCount( 2, \Cache\Engine\Mock::$cache );
	}

	public function test_time_expiration() {
		$key   = 'some_key';
		$cache = new \Cache\Engine\Cache( $key, $this->callable, array(), $this->engine );
		$cache->expiration( 10 );
		$cache->get();

		$this->assertEquals( \Cache\Engine\Mock::$time_expirations[ $key ], 10 );
	}

	public function test_defer() {
		global $wp_filter;

		$cache = new \Cache\Engine\Cache( 'some_key', $this->callable, array(), $this->engine );
		$cache->defer( true );
		$cache->get();

		$this->assertTrue( isset( $wp_filter['shutdown'] ) );
		$this->assertCount( 1, $wp_filter['shutdown'] );
	}

	public function test_all_backends_implement_ICache(){
		$backends = [
			'\Cache\Engine\Transients',
			'\Cache\Engine\WP_Object_Cache',
			'\Cache\Engine\Mock'
		];

		foreach ( $backends as $backend ) {
			$obj = new $backend();
			$this->assertInstanceOf( '\Cache\Engine\ICache', $obj );
		}
	}

	public function test_transient_engine() {
		$key    = 'some_key';
		$cache  = new \Cache\Engine\Cache( $key, $this->callable, array(), new \Cache\Engine\Transients() );

		$value1 = $cache->get();
		$value2 = $cache->get();

		$this->assertEquals( $value1, self::$value, 'Cache is not returning the correct data' );
		$this->assertEquals( $value1, $value2, 'Cache is not caching' );
		$this->assertEquals( $value1, get_transient( $key ), 'Cache is inconsistent with its backend' );

	}

	public function test_wp_object_cache_engine() {
		$key   = 'some_key';
		$cache = new \Cache\Engine\Cache( $key, $this->callable, array(), new \Cache\Engine\WP_Object_Cache() );

		$cache->expiration( 1 );

		$value1 = $cache->get();
		$value2 = $cache->get();

		$this->assertEquals( $value1, self::$value, 'Cache is not returning the correct data' );
		$this->assertEquals( $value1, $value2, 'Cache is not caching' );
		$this->assertEquals( $value1, wp_cache_get( $key ), 'Cache is inconsistent with its backend' );
	}


	/**
	 * Sample callable to hook into cache
	 */
	public static function callable_test( $arg = null ) {
		if ( empty( $arg ) ) {
			self::$value = "Time = " . rand();
		} else {
			self::$value = $arg;
		}

		echo self::$value;
	}

}


