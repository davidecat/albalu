<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Traits
 */

namespace AdTribes\PFE\Traits;

/**
 * Trait Trait_Instance
 *
 * @since 4.9.5
 */
trait Singleton_Trait {

    /**
     * Holds the class instance object
     *
     * @since 4.9.5
     * @access protected
     *
     * @var Singleton_Trait $instance object
     */
    protected static $instance;

    /**
     * Return an instance of this class
     *
     * @since 4.9.5☻
     * @access public
     *
     * @param array ...$args The arguments to pass to the constructor.
     * @return Singleton_Trait The class instance object
     */
    public static function instance( ...$args ) {

        if ( null === static::$instance ) {
            static::$instance = new static( ...$args );
        }

        return static::$instance;
    }

    /**
     * Magic get method
     *
     * @since 4.9.5
     * @access public
     *
     * @param string $key Class property to get.
     * @return null|mixed
     */
    public function __get( $key ) {

        return $this->$key ?? null;
    }
}
