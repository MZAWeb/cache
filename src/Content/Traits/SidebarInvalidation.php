<?php
namespace Cache\Content\Traits;

use Jeremeamia\SuperClosure\SerializableClosure;

trait SidebarInvalidation {

	/**
	 * @return $this
	 */
	public function expires_on_sidebar_save() {
		$args = $this->cache->args();
		$sidebar_name = $args[0];
		
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = new SerializableClosure( function ( $sidebar ) use ( $sidebar_name ) {
			return ( $sidebar === $sidebar_name );
		} );

		$invalidation->on_hook_with_callable( 'sidebar_updated', $callback, 10, 2 );

		return $this;
	}
}
