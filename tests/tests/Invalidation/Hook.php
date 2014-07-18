<?php

namespace Cache\Invalidation\Tests;

use Cache\Util\PersistedHook;
use Jeremeamia\SuperClosure\SerializableClosure;

class Hook extends \WP_UnitTestCase {

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

		delete_option( PersistedHook::RULES );

		$this->engine->flush();
	}

	public function test_invalidation_without_callback() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );

		$invalidation->on_hook( 'test_invalidation_without_callback', 99 );

		$value = (string) $this->base;

		PersistedHook::register_hooks();

		$this->assertCount( 1, \Cache\Engine\Mock::$cache );

		do_action( 'test_invalidation_without_callback' );

		$this->assertCount( 0, \Cache\Engine\Mock::$cache );

	}

	public function test_invalidation_with_true_callback() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );

		$invalidation->on_hook_with_callable( 'test_invalidation_with_true_callback', '__return_true', 99 );

		$value = (string) $this->base;

		PersistedHook::register_hooks();

		$this->assertCount( 1, \Cache\Engine\Mock::$cache );

		do_action( 'test_invalidation_with_true_callback' );

		$this->assertCount( 0, \Cache\Engine\Mock::$cache );

	}

	public function test_invalidation_with_false_callback() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );

		$invalidation->on_hook_with_callable( 'test_invalidation_with_false_callback', '__return_false', 99 );

		$value = (string) $this->base;

		PersistedHook::register_hooks();

		$this->assertCount( 1, \Cache\Engine\Mock::$cache );

		do_action( 'test_invalidation_with_false_callback' );

		$this->assertCount( 1, \Cache\Engine\Mock::$cache );
	}

	public function test_invalidation_with_filter_keeps_value() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );

		add_filter( 'test_invalidation_with_filter_keeps_value', function () {
			return 'yes!';
		} );

		$invalidation->on_hook( 'test_invalidation_with_filter_keeps_value', 99, 1 );

		$value = (string) $this->base;

		PersistedHook::register_hooks();

		$value = apply_filters( 'test_invalidation_with_filter_keeps_value', '' );

		$this->assertEquals( 'yes!', $value );
	}

	public function test_simple_hook_correct_data() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );

		$invalidation->on_hook( 'some_action', 99 );

		$rules = get_option( PersistedHook::RULES );

		$this->assertCount( 1, $rules );
		$this->assertTrue( isset( $rules['some_action'] ) );

		$rule = array_shift( $rules['some_action'] );

		$this->assertEquals( $rule['count_args'], 1 );
		$this->assertEquals( $rule['priority'], 99 );
	}

	public function test_hook_non_existent_callback() {

		$this->setExpectedException( '\Cache\InvalidCallableException' );

		$invalidation = new \Cache\Invalidation\Hook( $this->base );
		$invalidation->on_hook_with_callable( 'some_hook', 'some_function_that_hopefully_doesnt_exist' );
	}

	public function test_hook_with_object_callback() {

		$this->setExpectedException( '\Cache\InvalidCallableException' );

		$invalidation = new \Cache\Invalidation\Hook( $this->base );
		$invalidation->on_hook_with_callable( 'some_hook', array( $this, 'instance_method_callback' ) );
	}

	public function test_hook_with_closure_callback() {

		$this->setExpectedException( '\Cache\InvalidCallableException' );

		$invalidation = new \Cache\Invalidation\Hook( $this->base );
		$invalidation->on_hook_with_callable( 'some_hook', function () {
			echo 'woopy!';
		} );
	}

	public function test_hook_with_static_callback() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );
		$invalidation->on_hook_with_callable( 'some_hook', array( __CLASS__, 'callable_test' ) );

		$rules = get_option( PersistedHook::RULES );

		$this->assertCount( 1, $rules );
		$this->assertTrue( isset( $rules['some_hook'] ) );
	}

	public function test_hook_with_SuperClosure_callback() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );
		$invalidation->on_hook_with_callable( 'some_hook', new SerializableClosure( function () {
			echo 'woopy!';
		} ) );

		$rules = get_option( PersistedHook::RULES );

		$this->assertCount( 1, $rules );
		$this->assertTrue( isset( $rules['some_hook'] ) );
	}

	public function test_hook_with_SuperClosure_callback_is_recoverable() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );
		$invalidation->on_hook_with_callable( 'some_hook', new SerializableClosure( function () {
			return 'woopy!';
		} ) );

		$callable = get_option( 'cache_some_hook_' . $this->key );


		$callable = @unserialize( $callable );

		$this->assertTrue( is_callable( $callable ) );

		/**
		 * @var SerializableClosure $callable
		 */
		$value = $callable( 'woopy!' );

		$this->assertEquals( $value, 'woopy!' );
	}

	public function test_hook_correct_data() {
		$invalidation = new \Cache\Invalidation\Hook( $this->base );
		$invalidation->on_hook_with_callable( 'some_hook', array( __CLASS__, 'callable_test' ), 50, 3 );

		$rules = get_option( PersistedHook::RULES );

		$this->assertCount( 1, $rules );
		$this->assertTrue( isset( $rules['some_hook'] ) );

		$rule = array_shift( $rules['some_hook'] );

		$this->assertEquals( $rule['count_args'], 3 );
		$this->assertEquals( $rule['priority'], 50 );
	}

	public function instance_method_callback() {
		echo 'wooops!';
	}

	/**
	 * Sample callable to hook into cache
	 */
	public static function callable_test() {
		echo 'lalala';
	}

}


