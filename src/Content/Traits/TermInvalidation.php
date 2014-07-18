<?php
namespace Cache\Content\Traits;


use Jeremeamia\SuperClosure\SerializableClosure;

trait TermInvalidation {

	/**
	 * Invalidates the cache key on edit_term and create_term.
	 * If a list of taxonomies is provided, the invalidation will only
	 * happen if the edited/created term belongs to one of the given taxonomies.
	 *
	 * @param array $taxonomies Optional list of taxonomies
	 *
	 * @return $this
	 */
	public function expires_on_term_save( $taxonomies = array() ) {
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = null;

		if ( ! empty( $taxonomies ) ) {
			$callback = new SerializableClosure( function ( $term_id, $tt_id, $taxonomy ) use ( $taxonomies ) {
				return in_array( $taxonomy, $taxonomies );
			} );
		}

		$invalidation->on_hook_with_callable( 'edit_term', $callback, 10, 3 );
		$invalidation->on_hook_with_callable( 'create_term', $callback, 10, 3 );

		return $this;
	}
}