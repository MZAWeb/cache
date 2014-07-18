<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 7/6/14
 * Time: 10:47 PM
 */

namespace Cache\Engine;


interface ICache {

	public function get( $key );

	public function set( $key, $value, $expiration );

	public function delete( $key );
}