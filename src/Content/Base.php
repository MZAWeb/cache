<?php

/*
 * ToDo:
 *  For now this only works for string contents (templates, menus, etc).
 *  I will to extend it for storing objects too
 *
 */

namespace Cache\Content;

class Base {

	use Traits\TimeInvalidation;
	use Traits\HookInvalidation;
	use Traits\PostInvalidation;
	use Traits\CommentInvalidation;
	use Traits\OptionInvalidation;
	use Traits\TermInvalidation;
	use Traits\TemplateVars;

	/**
	 * @var \Cache\Engine\Cache
	 */
	public $cache = null;

	/**
	 * @var string|bool
	 */
	protected $content = false;

	/**
	 * @var bool
	 */
	protected $commited = false;

	/**
	 * @param \Cache\Engine\Cache $cache
	 */
	public function __construct( $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$this->commit();

		return $this->content;
	}


	/**
	 * ToDo
	 * @return $this
	 */
	public function with_lock() {
		$this->cache->lock( true );

		return $this;
	}

	public function append_to_key( $something ) {
		$key = $this->cache->key();
		$key = md5( $key . $something );
		$this->cache->key( $key );
	}

	/**
	 * ToDo
	 * @return $this
	 */
	public function defer() {
		$this->cache->defer( true );

		return $this;
	}

	/**
	 *
	 */
	public function refresh() {
		$this->commited = true;

		$this->content = $this->cache->refresh();
		$this->content = $this->apply_vars( $this->content );

		return $this;
	}

	/**
	 *
	 */
	protected function commit() {
		if ( ! $this->commited ) {
			$this->content = $this->cache->get();
			$this->content = $this->apply_vars( $this->content );
			$this->commited = true;
		}
	}



}