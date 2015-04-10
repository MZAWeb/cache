<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 7/5/14
 * Time: 3:41 AM
 */

namespace Cache\Engine;

use Cache\Util\PersistedHook;

class Cache {

	/**
	 * Cache Key
	 * @var string
	 */
	protected $key = '';

	/**
	 * Callback to populate this cache key
	 * @var callable
	 */
	protected $callable;

	/**
	 * Arguments for the callback to populate this cache key
	 * @var array
	 */
	protected $args;

	/**
	 * Expiration time in seconds
	 * @var int
	 */
	protected $expiration = 0;

	/**
	 * Wheter to call the callback inline or at shutdown
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Whether to allow multiple callbacks running at the same time
	 * NOT IMPLEMENTED
	 * ToDo
	 *
	 * @var bool
	 */
	protected $lock = false;

	/**
	 * The engine to handle the cache
	 * @var ICache
	 */
	protected $engine = null;

	/**
	 * @param  string      $key      Cache Key
	 * @param  callable    $callable Callback to populate this cache key. Needs to echo the content.
	 * @param  array       $args     Arguments for the callback
	 * @param  ICache|null $engine   Cache engine. Any class that implements ICache
	 *
	 */
	public function __construct( $key, $callable, $args, $engine = null ) {
		$this->key      = $key;
		$this->callable = $callable;
		$this->args     = $args;

		if ( empty( $engine ) || ! $engine instanceof ICache ) {
			$engine = new WP_Object_Cache();
		}

		$this->engine = $engine;
	}


	/**
	 * Returns the content for this cache.
	 * Tries to fetch from the Cache Engine, and if not there
	 * will use the provided callback to generate the content.
	 *
	 * @return bool|string
	 */
	public function get() {
		
		$content = $this->engine->get( $this->key );


		if ( $content === false ) {

			PersistedHook::remove_hook_by_key( $this->key );

			if ( $this->defer ) {
				add_action( 'shutdown', array( $this, '_generate' ) );
			} else {
				$content = $this->_generate();
			}
		}

		return $content;
	}

	/**
	 * Deletes the current cache key and generates the content again.
	 * @return bool|string
	 */
	public function refresh() {
		
		$this->engine->delete( $this->key );

		return $this->get();
	}

	/**
	 * Getter / Setter for the key.
	 *
	 * @param string|null $key
	 *
	 * @return string
	 */
	public function key( $key = null ) {
		if ( $key !== null ) {
			$this->key = $key;
		}

		return $this->key;
	}

	/**
	 * Getter for the args
	 * @return array
	 */
	public function args() {
		return $this->args;
	}

	/**
	 * Getter / Setter for the expiration time in seconds
	 * @param int|null $seconds
	 *
	 * @return int
	 */
	public function expiration( $seconds = null ) {
		if ( $seconds !== null ) {
			$this->expiration = $seconds;
		}

		return $this->expiration;
	}

	/**
	 * Getter / Setter for whether the generation needs to happen on shutdown
	 * @param bool|null $defer
	 *
	 * @return bool
	 */
	public function defer( $defer = null ) {
		if ( $defer !== null ) {
			$this->defer = $defer;
		}

		return $this->defer;
	}

	/**
	 * Getter / Setter for whether only one callback should be called at a given time
	 * @param bool|null $lock
	 *
	 * @return bool
	 */
	public function lock( $lock = null ) {
		if ( $lock !== null ) {
			$this->lock = $lock;
		}

		return $this->lock;
	}

	/**
	 * Generates the content for this cache key using the
	 * provided callback.
	 *
	 * Shouldn't be called directly. Needs to be public so it can be
	 * called from the shutdown action if defer is true.
	 *
	 * @return string
	 */
	public function _generate() {
		ob_start();
		call_user_func_array( $this->callable, $this->args );
		$content = ob_get_clean();

		$this->engine->set( $this->key, $content, $this->expiration );

		return $content;
	}

	public function engine() {
		return get_class( $this->engine );
	}

} 