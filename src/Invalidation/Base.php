<?php

namespace Cache\Invalidation;


class Base {

	/**
	 * @var \Cache\Content\Base
	 */
	protected $content = null;

	public function __construct( $content ) {
		$this->content = $content;
	}

}