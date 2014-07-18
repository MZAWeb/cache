<?php

namespace Cache\Invalidation;

use Cache\InvalidCallableException;
use Cache\Util\PersistedHook;

class Hook extends Base {

	/**
	 *
	 * Hook is any WordPress hook after plugins_loaded.
	 * It can be either an action or a filter. If it's a filter, the handler
	 * will automatically return the same value, so your data is not modified.
	 *
	 * Callable needs to return a boolean. If the return value is true, it means
	 * the cache needs to be invalidated a the cache key will be deleted. If returns
	 * false, nothing will happen.
	 *
	 * Callable needs to be one of the following:
	 *  1) An instance of \Jeremeamia\SuperClosure\SerializableClosure, or
	 *  2) An array with a static method callback (can't use an instance)
	 *  3) A string with a global function callback
	 *
	 *  the SerializableClosure is preferred as it allows you to pass arbitrary values
	 *  but you should be aware that it uses Reflection, and it makes it slower.
	 *
	 *  All options will get the same parameters that the hook gets
	 *
	 *  Samples:
	 *
	 *  1) new SerializableClosure( function ( $post ) use ( $some_var ) {
	 *          return ( $post->post_type == $some_var ) ? true : false;
	 *     } );
	 *
	 *  2) array( "SomeClass", "static_method" )
	 *  3) "__return_true"
	 *
	 * @param                                                           $hook
	 * @param \Jeremeamia\SuperClosure\SerializableClosure|array|string $callable
	 * @param int                                                       $priority
	 * @param int                                                       $count_args
	 *
	 * @throws \Cache\InvalidCallableException
	 */
	public function on_hook_with_callable( $hook, $callable, $priority = 10, $count_args = 1 ) {

		if ( $callable != null && ! PersistedHook::validate_callable( $callable ) ) {
			throw new InvalidCallableException( "Invalid callable" );
		}

		$cache_key = $this->content->cache->key();
		$engine    = $this->content->cache->engine();

		$callback = new \Jeremeamia\SuperClosure\SerializableClosure( function () use ( $callable, $cache_key, $engine ) {

			$callback = maybe_unserialize( $callable );

			if ( $callback !== null ) {
				$result = call_user_func_array( $callback, func_get_args() );
			} else {
				$result = true;
			}
			
			if ( $result ) {
				$engine = new $engine();
				$engine->delete( $cache_key );
			} else {
				add_filter( 'persisted_hook_skip_remove', '__return_true' );
			}

			$value = func_num_args() > 0 ? func_get_arg( 0 ) : null;

			return $value;
		} );

		new PersistedHook( $hook, $callback, $priority, $count_args, $cache_key );

	}

	public function on_hook( $hook, $priority = 10 ) {
		$this->on_hook_with_callable( $hook, null, $priority, 1 );
	}


}