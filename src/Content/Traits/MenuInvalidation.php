<?php
namespace Cache\Content\Traits;

use Jeremeamia\SuperClosure\SerializableClosure;

trait MenuInvalidation {

	/**
	 * Invalidates the cache key when the cached menu is updated
	 *
	 * @return $this
	 */
	public function expires_on_menu_save() {
		$args         = $this->cache->args();
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = new SerializableClosure( function ( $id, $data = array() ) use ( $args ) {

			$args = array_shift( $args );

			if ( ! empty( $args['menu'] ) && ! empty( $data['menu-name'] ) && $args['menu'] === $data['menu-name'] ) {
				return true;
			}

			if ( ! empty( $args['theme_location'] ) ) {
				$locations = get_nav_menu_locations();
				if ( empty( $locations[ $args['theme_location'] ] ) ) {
					return false;
				}
				if ( $locations[ $args['theme_location'] ] == $id ) {
					return true;
				}
			}

			return false;

		} );

		$invalidation->on_hook_with_callable( 'wp_update_nav_menu', $callback, 10, 2 );

		return $this;
	}
}