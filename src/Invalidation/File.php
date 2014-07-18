<?php
namespace Cache\Invalidation;


class File extends Base {

	public function on_file_modified( $path ) {

		if ( !file_exists( $path ) ) {
			return;
		}

		$md5_file = md5_file( $path );

		$key = $this->content->cache->key();

		$this->content->cache->key( md5( $key . $md5_file ) );

	}
}