<?php

namespace Cache\Content\Tests;

class Sidebar extends \WP_UnitTestCase {

	public $plugins = array( 'cache/bootstrap.php' );
	public $cache   = null;
	public $key     = 'testing_cache_key';

	static $time    = null;

	public function setUp() {
		parent::setUp();

		if ( ! function_exists( "activate_plugin" ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $this->plugins as $plugin ) {
			activate_plugin( $plugin );
		}

		$this->cache = new \Cache\Engine\Cache( $this->key, array( __CLASS__, 'callable_test' ), array(), new \Cache\Engine\Mock() );
	}

	/**
	 * Make sure we don't delete a trait by mistake
	 */
	public function test_use_traits() {
		$sidebar = new \Cache\Content\Sidebar( $this->cache );

		$wanted_traits = [
			'Cache\Content\Traits\SidebarInvalidation'
		];

		$actual_traits = array_values( class_uses( $sidebar ) );

		$this->assertEquals( $wanted_traits, $actual_traits );
	}

	/**
	 * All public methods should return the same object
	 * to allow for chainable calls.
	 */
	public function test_chainability() {

		// Let's test only the ones implemented in Menu.
		// The ones in Base are tested elsewhere.
		$base    = new \Cache\Content\Base( $this->cache );
		$sidebar = new \Cache\Content\Sidebar( $this->cache );

		$base_methods    = get_class_methods( $base );
		$sidebar_methods = get_class_methods( $sidebar );

		$to_test = array_diff( $sidebar_methods, $base_methods );

		// Call every public method
		foreach ( $to_test as $method ) {

			// Ignore magic methods
			if ( strstr( $method, '__' ) !== false ) {
				continue;
			}

			$value = call_user_func_array( array( $sidebar, $method ), array( 1, array( '\Cache\Content\Tests\Base', 'callable_test' ), 3, 4, 5 ) );
			$this->assertSame( $sidebar, $value, 'Sidebar->' . $method . ' should return $this' );
		}
	}

	/**
	 * Sample callable to hook into cache
	 */
	public static function callable_test() {
		self::$time = "Time = " . rand();

		echo self::$time;
	}

}


