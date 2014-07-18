<?php
namespace Cache\Engine;


class Transients implements ICache {

	public function get( $key ) {
		return get_transient( $key );
	}

	public function set( $key, $value, $expiration ) {
		set_transient( $key, $value, $expiration );
	}

	public function delete( $key ) {
		delete_transient( $key );
	}
} 