<?php

namespace Cache\Invalidation\Tests;

class File extends \WP_UnitTestCase {

	public $plugins  = array( 'cache/bootstrap.php' );
	public $cache    = null;
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

		$this->cache = new \Cache\Engine\Cache( $this->key, $this->callable, array(), new \Cache\Engine\Mock() );
	}

	public function test_file_change_changes_key() {
		$file         = WP_CONTENT_DIR . '/plugins/cache/tests/data/changes.txt';
		$base         = new \Cache\Content\Base( $this->cache );
		$invalidation = new \Cache\Invalidation\File( $base );

		file_put_contents( $file, rand() );
		$invalidation->on_file_modified( $file );
		$_ = (string) $base;

		$key2 = $base->cache->key();

		file_put_contents( $file, rand() );
		$invalidation->on_file_modified( $file );
		$_ = (string) $base;

		$key3 = $base->cache->key();

		$this->assertNotEquals( $this->key, $key2 );
		$this->assertNotEquals( $key2, $key3 );

	}

	/**
	 * Sample callable to hook into cache
	 */
	public static function callable_test() {
		self::$time = "Time = " . rand();

		echo self::$time;
	}

}


