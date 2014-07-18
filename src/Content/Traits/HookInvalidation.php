<?php
namespace Cache\Content\Traits;


trait HookInvalidation {

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
	 * @return $this
	 */
	public function expires_on( $hook, $callable = null, $priority = 10, $count_args = 1 ) {
		$invalidation = new \Cache\Invalidation\Hook( $this );
		$invalidation->on_hook_with_callable( $hook, $callable, $priority, $count_args );

		return $this;
	}
}