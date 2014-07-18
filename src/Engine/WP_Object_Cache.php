<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 7/6/14
 * Time: 10:52 PM
 */

namespace Cache\Engine;


class WP_Object_Cache implements ICache {

	public $group = '';

	public function get( $key ) {
		return wp_cache_get( $key, $this->group );
	}

	public function set( $key, $value, $expiration ) {
		wp_cache_set( $key, $value, $this->group, $expiration );
	}

	public function delete( $key ) {
		wp_cache_delete( $key, $this->group );
	}
} 