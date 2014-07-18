<?php

namespace Cache\Content\Traits;

use Jeremeamia\SuperClosure\SerializableClosure;

trait PostInvalidation {

	/**
	 * Invalidates the cache on post_save.
	 *
	 * If you provide a list of post IDs, the key will get invalidated only
	 * when saving any of those posts.
	 *
	 * @param array $post_ids optional array of post ids on which you want to invalidate
	 *
	 * @return $this
	 */
	public function expires_on_post_save( $post_ids = array() ) {
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = null;

		if ( ! empty( $post_ids ) ) {

			$callback = new SerializableClosure( function ( $post_id ) use ( $post_ids ) {
				if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
					return false;
				}

				return in_array( $post_id, $post_ids );
			} );

		}

		$invalidation->on_hook_with_callable( 'save_post', $callback );

		return $this;
	}

	/**
	 * On post_save check if the post belongs to any of the provided taxonomies.
	 * If it does, the cache key will get invalidated.
	 *
	 * @param array $taxonomies array of taxonomy names on which you want to invalidate
	 *
	 * @return $this
	 */
	public function expires_on_post_save_in_taxonomy( $taxonomies ) {
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = new SerializableClosure(
			function ( $post_id ) use ( $taxonomies ) {
				if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
					return false;
				}

				foreach ( $taxonomies as $taxonomy ) {
					$terms = get_the_terms( $post_id, $taxonomy );
					if ( ! empty( $terms ) ) {
						return true;
					}
				}

				return false;
			}
		);

		$invalidation->on_hook_with_callable( 'save_post', $callback );

		return $this;
	}


}