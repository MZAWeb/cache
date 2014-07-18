<?php

namespace Cache\Util\Tests;

use Jeremeamia\SuperClosure\SerializableClosure;

class PersistedHook extends \WP_UnitTestCase {

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

	public static $skip_count = 0;

	public function setUp() {
		parent::setUp();

		if ( ! function_exists( "activate_plugin" ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $this->plugins as $plugin ) {
			activate_plugin( $plugin );
		}

		delete_option( \Cache\Util\PersistedHook::RULES );
	}

	public function test_add_hook() {
		new \Cache\Util\PersistedHook( 'test_add_hook', new SerializableClosure( function () {
			echo 'wooot';
		} ), 10, 1 );

		\Cache\Util\PersistedHook::register_hooks();

		ob_start();

		do_action( 'test_add_hook' );

		$value = ob_get_clean();

		$this->assertEquals( 'wooot', $value );
	}

	public function test_remove_hook() {
		new \Cache\Util\PersistedHook( 'test_remove_hook', new SerializableClosure( function () {
			return 'wooot';
		} ), 10, 1 );

		\Cache\Util\PersistedHook::register_hooks();

		do_action( 'test_remove_hook' );

		$this->assertCount( 0, get_option( \Cache\Util\PersistedHook::RULES ) );
	}

	public function test_only_one_skip_remove_hook() {
		new \Cache\Util\PersistedHook( 'test_only_one_skip_remove_hook', new SerializableClosure( function () {

			if ( PersistedHook::$skip_count == 0 ) {
				add_filter( 'persisted_hook_skip_remove', '__return_true' );
				PersistedHook::$skip_count ++;
			}

			return 'wooot';
		} ), 10, 1 );

		\Cache\Util\PersistedHook::register_hooks();

		do_action( 'test_only_one_skip_remove_hook' );

		$this->assertCount( 1, get_option( \Cache\Util\PersistedHook::RULES ) );

		do_action( 'test_only_one_skip_remove_hook' );

		$this->assertCount( 0, get_option( \Cache\Util\PersistedHook::RULES ) );
	}


	public function test_cant_use_instance_callback(){
		$this->setExpectedException('\Cache\InvalidCallableException');

		new \Cache\Util\PersistedHook( 'some_hook', array( $this, 'callable_instance_test' ), 10, 1 );
	}

	public function test_cant_use_invalid_callback(){
		$this->setExpectedException('\Cache\InvalidCallableException');

		new \Cache\Util\PersistedHook( 'some_hook', 'function_that_doesnt_exist_hopefully', 10, 1 );
	}


	public function callable_instance_test(){
		return 'wooot';
	}

	public static function callable_return_test() {
		return 'wooot';
	}

	public static function callable_echo_test() {
		echo 'wooot';
	}



}


