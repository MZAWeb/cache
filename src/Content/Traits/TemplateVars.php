<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 7/7/14
 * Time: 1:54 AM
 */

namespace Cache\Content\Traits;


trait TemplateVars {

	/**
	 * @var array
	 */
	protected $vars = array();

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function with_var( $key, $value ) {
		$this->vars[ $key ] = $value;

		return $this;
	}

	/**
	 * @param array $vars
	 *
	 * @return $this
	 */
	public function with_vars( $vars ) {
		if ( ! is_array( $vars ) ) {
			$vars = (array) $vars;
		}

		$this->vars = array_merge( $this->vars, $vars );

		return $this;
	}


	/**
	 * @param $content
	 *
	 * @return mixed
	 */
	protected function apply_vars( $content ) {
		foreach ( $this->vars as $key => $value ) {
			$content = $this->apply_var( $key, $value, $content );
		}

		return $content;
	}


	/**
	 * @param $key
	 * @param $value
	 * @param $content
	 *
	 * @return mixed
	 */
	protected function apply_var( $key, $value, $content ) {
		return str_replace( '{' . $key . '}', $value, $content );
	}

} 