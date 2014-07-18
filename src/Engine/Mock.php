<?php
namespace Cache\Engine;


class Mock implements ICache {
	public static $cache            = array();
	public static $time_expirations = array();

	public function get( $key ) {
		return isset( self::$cache[ $key ] ) ? self::$cache[ $key ] : false;
	}

	public function set( $key, $value, $expiration ) {
		self::$cache[ $key ]            = $value;
		self::$time_expirations[ $key ] = $expiration;
	}

	public function delete( $key ) {
		unset( self::$cache[ $key ] );
		unset( self::$time_expirations[ $key ] );
	}

	public function flush(){
		self::$cache = array();
		self::$time_expirations = array();
	}
} 