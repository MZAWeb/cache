<?php
/*
 Plugin Name: Cache
 Plugin URI: http://tri.be
 Description: Cache Framework. Documentation coming soon.
 Author: Daniel Dvorkin
 Version: 0.1
 Author URI: http://danieldvork.in/
 */

include 'vendor/autoload.php';
include 'src/Exceptions.php';
include 'src/Cache.php';

add_action( 'plugins_loaded', function () {
	\Cache\Util\PersistedHook::register_hooks();
} );

/**
 * PSR-4ish autoload handler
 *
 * @param $class
 */
function cache_autoload( $class ) {
	$parts = explode( '\\', $class );

	if ( empty( $parts ) || $parts[0] != 'Cache' || empty( $parts[1] ) ) {
		return;
	}

	$parts[0] = 'src';

	$path = join( DIRECTORY_SEPARATOR, $parts );
	$path = $path . '.php';
	$path = plugin_dir_path( __FILE__ ) . $path;

	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

spl_autoload_register( 'cache_autoload' );