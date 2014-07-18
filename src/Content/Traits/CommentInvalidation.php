<?php

namespace Cache\Content\Traits;

use Jeremeamia\SuperClosure\SerializableClosure;

trait CommentInvalidation {

	/**
	 * Invalidates the cache key on edit_comment and wp_insert_comment.
	 * If a list of post IDs is provided, the invalidation will only
	 * happen if the edited/created comments belongs to one of the given posts.
	 *
	 * @param array $post_ids Optional list of posts
	 *
	 * @return $this
	 */
	public function expires_on_comment_save( $post_ids = array() ) {
		$invalidation = new \Cache\Invalidation\Hook( $this );

		$callback = null;

		if ( ! empty( $post_ids ) ) {
			$callback = new SerializableClosure( function ( $id ) use ( $post_ids ) {
				$comment = get_comment( $id );

				return in_array( $comment->comment_post_ID, $post_ids );
			} );
		}

		$invalidation->on_hook_with_callable( 'edit_comment', $callback );
		$invalidation->on_hook_with_callable( 'wp_insert_comment', $callback );

		return $this;
	}
}