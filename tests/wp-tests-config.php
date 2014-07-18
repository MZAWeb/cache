<?php

define( 'WP_TESTS_DOMAIN', 'hls.tribe' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
}

/* Path to the WordPress codebase you'd like to test. Add a backslash in the end. */
define( 'ABSPATH', dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp/' );
define( 'WP_CONTENT_DIR', dirname( ABSPATH ) . '/content' );
define( 'WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content' );


// Test with multisite enabled: (previously -m)
// define( 'WP_TESTS_MULTISITE', true );

// Force known bugs: (previously -f)
// define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Test with WordPress debug mode on (previously -d)
// define( 'WP_DEBUG', true );

// ** MySQL settings ** //

// This configuration file will be used by the copy of WordPress being tested.
// wordpress/wp-config.php will be ignored.

// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.

$db    = isset( $_SERVER['MYSQL_PORT_3306_TCP_ADDR'] ) ? $_SERVER['MYSQL_PORT_3306_TCP_ADDR'] : '127.0.0.1';
$cache = isset( $_SERVER['MEMCACHED_PORT_11211_TCP_ADDR'] ) ? $_SERVER['MEMCACHED_PORT_11211_TCP_ADDR'] : '127.0.0.1';

define( 'DB_NAME', 'HLS_Tests' );
define( 'DB_USER', 'mzaweb' );
define( 'DB_PASSWORD', 'manise' );
define( 'DB_HOST', $db );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix  = 'wptests_';   // Only numbers, letters, and underscores please!


define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );

global $memcached_servers;
$memcached_servers = array(
	array(
		$cache, // Memcached server IP address
		11211 // Memcached server port
	)
);