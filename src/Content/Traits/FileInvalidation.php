<?php

namespace Cache\Content\Traits;

trait FileInvalidation {

	/**
	 * Expires the cache if the PHP file for the template part is changed
	 *
	 * @return $this
	 */
	public function expires_on_file_edited() {
		$args = $this->cache->args();
		if ( ! isset( $args[0] ) ) {
			return $this;
		}

		$file = trailingslashit( ( $args[0] ) );

		if ( isset( $args[1] ) ) {
			$file .= $args[1];
		}

		$file = locate_template( $file );

		$invalidation = new \Cache\Invalidation\File( $this );
		$invalidation->on_file_modified( $file );

		return $this;
	}
}