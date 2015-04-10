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


	/**
	 * Invalidates the cache key when any
	 *
	 * @return $this
	 */
	public function expires_on_post_changes_slug() {
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = new SerializableClosure( function (){

			if ( !empty( $_POST['action'] ) && $_POST['action'] == 'sample-permalink' ) {
				return true;
			}

			return false;
		} );

		$invalidation->on_hook_with_callable( 'init', $callback, 10 );

		return $this;
	}

	/**
	 * Invalidates the cache key when any post gets a new title or parent
	 *
	 * @return $this
	 */
	public function expires_on_post_updated() {
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = new SerializableClosure( function ( $post_ID, $post_after, $post_before ) {

			if ( $post_after->post_title !== $post_before->post_title ||
			     $post_after->post_parent !== $post_before->post_parent ) {
				return true;
			}

			return false;
		} );

		$invalidation->on_hook_with_callable( 'post_updated', $callback, 10, 3 );

		return $this;
	}
}