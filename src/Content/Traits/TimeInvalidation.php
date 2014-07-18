<?php
namespace Cache\Content\Traits;

trait TimeInvalidation {

	/**
	 * Expires the cache in the given amount of seconds
	 *
	 * @param $seconds
	 *
	 * @return $this
	 */
	public function expires_in_seconds( $seconds ) {
		$invalidation = new \Cache\Invalidation\Time( $this );
		$invalidation->in_seconds( $seconds );

		return $this;
	}

	/**
	 * Expires the cache in the given amount of minutes
	 *
	 * @param $minutes
	 *
	 * @return $this
	 */
	public function expires_in_minutes( $minutes ) {
		$invalidation = new \Cache\Invalidation\Time( $this );
		$invalidation->in_minutes( $minutes );

		return $this;
	}

	/**
	 * Expires the cache in the given amount of hours
	 *
	 * @param $hours
	 *
	 * @return $this
	 */
	public function expires_in_hours( $hours ) {
		$invalidation = new \Cache\Invalidation\Time( $this );
		$invalidation->in_hours( $hours );

		return $this;
	}

	/**
	 * Expires the cache in the given amount of days
	 *
	 * @param $days
	 *
	 * @return $this
	 */
	public function expires_in_days( $days ) {
		$invalidation = new \Cache\Invalidation\Time( $this );
		$invalidation->in_days( $days );

		return $this;
	}
}