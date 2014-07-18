<?php
namespace Cache\Content\Traits;

use Jeremeamia\SuperClosure\SerializableClosure;

trait OptionInvalidation {


	/**
	 * Invalidates the cache key on update_option for a given option name
	 *
	 * @param $option_name
	 *
	 * @return $this
	 */
	public function expires_on_option_save( $option_name ) {
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = new SerializableClosure( function ( $option ) use ( $option_name ) {
			return ( $option === $option_name );
		} );

		$invalidation->on_hook_with_callable( 'update_option', $callback );

		return $this;
	}

}