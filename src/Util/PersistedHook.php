<?php

namespace Cache\Util;

use Cache\InvalidCallableException;
use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Class PersistedHook
 *
 * This should be moved to a separated package at some point.
 *
 * It's like a standard WordPress hook but it'll keep getting added
 * until it's executed, even across multiple requests.
 *
 * @package Cache\Util
 */
class PersistedHook {

	/**
	 * Option key where we're storing the hooks
	 */
	const RULES = 'persisted_hooks';

	/**
	 *
	 * Hook can be any WordPress hook after plugins_loaded.
	 *
	 * Callable needs to be one of the following:
	 *  1) An instance of \Jeremeamia\SuperClosure\SerializableClosure, or
	 *  2) An array with a static method callback (can't use an instance)
	 *  3) A string with a global function callback
	 *
	 *  the SerializableClosure is preferred as it allows you to pass arbitrary values
	 *  but you should be aware that it uses Reflection, and it makes it slower.
	 *
	 *  All options will get the same parameters that the hook gets
	 *
	 *  Samples:
	 *
	 *  1) new SerializableClosure( function ( $post ) use ( $some_var ) {
	 *          return ( $post->post_type == $some_var ) ? true : false;
	 *     } );
	 *
	 *  2) array( "SomeClass", "static_method" )
	 *  3) "__return_true"
	 *
	 *  $unique_id is given to prevent registering the same Persisted Hook twice.
	 *  be aware that if you don't provide one, it will be generated by serializing
	 *  all the args, and it's slow.
	 *
	 * @param string                                                    $hook
	 * @param \Jeremeamia\SuperClosure\SerializableClosure|array|string $callable
	 * @param int                                                       $priority
	 * @param int                                                       $count_args
	 * @param null                                                      $unique_id
	 *
	 * @throws \Cache\InvalidCallableException
	 */
	public function __construct( $hook, $callable, $priority, $count_args, $unique_id = null ) {

		if ( ! self::validate_callable( $callable ) ) {
			throw new InvalidCallableException( "Invalid callable" );
		}

		$data = array(
			'priority'   => $priority,
			'count_args' => $count_args,
		);

		$this->add_hook( $hook, $data, $callable, $unique_id );

	}

	/**
	 * Stores a new hook in the database
	 *
	 * @param string $hook
	 * @param  array $data
	 * @param        $callable
	 * @param null   $unique_id
	 *
	 * @return string
	 */
	protected function add_hook( $hook, $data, $callable, $unique_id = null ) {
		$rules = get_option( self::RULES, array() );

		if ( empty( $unique_id ) ) {
			$unique_id = $hook . md5( maybe_serialize( $data ) . maybe_serialize( $callable ) );
		}

		if ( ! isset( $rules[ $hook ][ $unique_id ] ) ) {
			$rules[ $hook ][ $unique_id ] = $data;
			update_option( self::RULES, $rules );
			update_option( 'cache_' . $hook . '_' . $unique_id, $callable );
		}
	}

	public static function remove_hook( $hook, $key ) {

		$rules = get_option( self::RULES, array() );

		if ( isset( $rules[ $hook ][ $key ] ) ) {
			unset( $rules[ $hook ][ $key ] );
			delete_option( 'cache_' . $hook . '_' . $key );
		}

		if ( empty( $rules[ $hook ] ) ) {
			unset( $rules[ $hook ] );
		}

		update_option( self::RULES, $rules );
	}

	public static function remove_hook_by_key( $key ) {

		$rules = get_option( self::RULES, array() );

		foreach ( $rules as $hook => $keys ) {

			if ( ! array_key_exists( $key, $keys ) ) {
				continue;
			}

			unset( $rules[ $hook ][ $key ] );
			delete_option( 'cache_' . $hook . '_' . $key );

			if ( empty( $rules[ $hook ] ) ) {
				unset( $rules[ $hook ] );
			}
		}

		update_option( self::RULES, $rules );
	}

	/**
	 *
	 */
	public static function register_hooks() {
		$rules = get_option( self::RULES, array() );

		foreach ( $rules as $hook => $keys ) {

			foreach ( $keys as $key => $rule ) {

				$callback = function () use ( $key, $hook ) {

					$callable = get_option( 'cache_' . $hook . '_' . $key );
					$callable = @unserialize( $callable );

					$value = null;

					if ( is_callable( $callable ) ) {

						try {
							$value = call_user_func_array( $callable, func_get_args() );
						} catch ( \Exception $e ) {
							error_log( print_r( $e->getMessage(), true ) );
						}

					}

					$skip = apply_filters( 'persisted_hook_skip_remove', false, $key );
					
					if ( ! $skip ) {
						self::remove_hook( $hook, $key );
					} else {
						remove_all_filters( 'persisted_hook_skip_remove' );
					}

					return $value;
				};

				add_filter( $hook, $callback, $rule['priority'], $rule['count_args'] );

			}
		}
	}

	/**
	 * Makes sure that the callable is in one of the allowed formats.
	 *
	 * See phpDoc for add_filter.
	 *
	 * @param $callable
	 *
	 * @return bool
	 */
	public static function validate_callable( $callable ) {

		if ( is_a( $callable, '\Jeremeamia\SuperClosure\SerializableClosure' ) ) {
			return true;
		}

		if ( ! is_callable( $callable ) ) {
			return false;
		}

		if ( ! is_string( $callable ) && ! is_array( $callable ) ) {
			return false;
		}

		if ( is_object( $callable[0] ) ) {
			return false;
		}

		return true;
	}


}
