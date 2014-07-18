<?php


/**
 * Class Cache
 *
 * Wrapper around the most common functionality
 * with easy access via static method calls.
 *
 */
class Cache {

	/**
	 * Caches any arbitrary callback
	 *
	 * @param callable $callable
	 * @param array    $args
	 *
	 * @return \Cache\Content\Base
	 */
	public static function callback( $callable, $args = array() ) {
		$key   = md5( $callable . md5( $args ) );
		$cache = new Cache\Engine\Cache( $key, $callable, $args );

		return new Cache\Content\Base( $cache );
	}

	/**
	 * Caches a call to get_template_part
	 *
	 * @param string $slug
	 * @param string $name
	 * @param string $context Allows for different caches of the same file depending context (ie: different loops)
	 *
	 * @return \Cache\Content\Template
	 */
	public static function template_part( $slug, $name = '', $context = '' ) {

		$key   = md5( $slug . $name . $context );
		$arg   = array( $slug, $name );
		$cache = new Cache\Engine\Cache( $key, 'get_template_part', $arg );

		return new Cache\Content\Template( $cache );
	}

	/**
	 * Caches a call to wp_nav_menu
	 *
	 * @param array $args Same as you'd pass to wp_nav_menu
	 *
	 * @return \Cache\Content\Menu
	 */
	public static function menu( $args ) {
		$key   = md5( maybe_serialize( $args ) );
		
		$cache = new Cache\Engine\Cache( $key, 'wp_nav_menu', array( $args ) );

		return new Cache\Content\Menu( $cache );
	}

	/**
	 * Caches a call to dynamic_sidebar
	 *
	 * NOT IMPLEMENTED YET
	 *
	 * @param int|string $index Same as you'd pass to dynamic_sidebar
	 *
	 * @return \Cache\Content\Sidebar
	 */
	public static function sidebar( $index = 1 ) {
		$key   = 'sidebar-' . $index;
		$arg   = array( $index );
		$cache = new \Cache\Engine\Cache( $key, 'dynamic_sidebar', $arg );


		return new Cache\Content\Sidebar( $cache );
	}


}
