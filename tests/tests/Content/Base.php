<?php

namespace Cache\Content\Tests;

class Base extends \WP_UnitTestCase {

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
		$base = new \Cache\Content\Base( $this->cache );

		$wanted_traits = [
			'Cache\Content\Traits\TimeInvalidation',
			'Cache\Content\Traits\HookInvalidation',
			'Cache\Content\Traits\PostInvalidation',
			'Cache\Content\Traits\CommentInvalidation',
			'Cache\Content\Traits\OptionInvalidation',
			'Cache\Content\Traits\TermInvalidation',
			'Cache\Content\Traits\TemplateVars',
		];

		$actual_traits = array_values( class_uses( $base ) );

		$this->assertEquals( $wanted_traits, $actual_traits );
	}

	/**
	 * All public methods should return the same object
	 * to allow for chainable calls.
	 */
	public function test_chainability() {
		$base = new \Cache\Content\Base( $this->cache );

		// Call every public method
		foreach ( get_class_methods( $base ) as $method ) {

			// Ignore magic methods
			if ( strstr( $method, '__' ) !== false ) {
				continue;
			}

			$value = call_user_func_array( array( $base, $method ), array( 1, array( '\Cache\Content\Tests\Base', 'callable_test' ), 3, 4, 5 ) );
			$this->assertSame( $base, $value, 'Base->' . $method . ' should return $this' );
		}
	}

	/**
	 * Make sure Base objects can be echoed
	 */
	public function test_it_can_be_echoed() {
		$base = new \Cache\Content\Base( $this->cache );

		ob_start();
		echo $base;
		$value = ob_get_clean();

		$this->assertEquals( $value, self::$time );
	}

	/**
	 * Base objets should behave as a string of the cached content
	 */
	public function test_it_works_like_a_string() {
		$base = new \Cache\Content\Base( $this->cache );

		$value = (string) $base;
		$this->assertEquals( self::$time, $value, "Can't be used as string" );

		$value = str_replace( 'Time', 'Random', $base );
		$this->assertContains( 'Random', $value, $value . " doesn't contain the word Random" );

		$value = 'Testing' . $base;

		$this->assertEquals( $value, 'Testing' . self::$time, "Failed to concatenate with a string" );
	}

	/**
	 * Make sure it caches succesfully
	 */
	public function test_should_actually_cache() {
		$base = new \Cache\Content\Base( $this->cache );

		$first_value = (string) $base;
		$second_value = (string) $base;

		$this->assertEquals( $first_value, $second_value, "Cache value should be persisted" );
	}

	/**
	 * Base objets should allow to refresh content
	 */
	public function test_should_be_able_to_refresh() {
		$base        = new \Cache\Content\Base( $this->cache );

		$first_value = (string) $base;
		$base->refresh();
		$second_value = (string) $base;

		$this->assertNotEquals( $first_value, $second_value, "Cache value should be refreshed" );
	}

	/**
	 * Sample callable to hook into cache
	 */
	public static function callable_test() {
		self::$time = "Time = " . rand();

		echo self::$time;
	}

}


