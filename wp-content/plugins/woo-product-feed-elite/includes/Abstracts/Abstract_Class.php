<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE
 */

namespace AdTribes\PFE\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Class
 */
abstract class Abstract_Class {

    /**
     * Magic get method.
     *
     * @since 4.9.5
     * @access public
     *
     * @param string $key The key to get.
     * @return null|mixed
     */
    public function __get( $key ) {

        return $this->$key ?? null;
    }

    /**
     * Run the class
     *
     * @since 4.9.5
     * @access public
     */
    abstract public function run();
}
