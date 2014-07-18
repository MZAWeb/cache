<?php

namespace Cache\Invalidation;


class Time extends Base {

	protected $seconds = 0;

	public function in_seconds( $seconds ) {
		$this->content->cache->expiration( $seconds );
	}

	public function in_minutes( $minutes ) {
		$this->in_seconds( $minutes * 60 );
	}

	public function in_hours( $hours ) {
		$this->in_minutes( $hours * 60 );
	}

	public function in_days( $days ) {
		$this->in_hours( $days * 24 );
	}

}