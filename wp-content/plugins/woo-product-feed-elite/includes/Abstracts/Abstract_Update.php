<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Abstracts
 */

namespace AdTribes\PFE\Abstracts;

use AdTribes\PFE\Helpers\Helper;

/**
 * Abstract Update class.
 * Update classes should extend this abstract class and implement the `actions()` method. It should do whatever is
 * needed to update the plugin except updating the version number in database as that is already done by default by the
 * abstract class, unless, `run()` method is overridden.
 *
 * @since 5.3.5
 */
abstract class Abstract_Update extends Abstract_Class {

    /**
     * Run updates.
     *
     * @since 5.3.5
     */
    public function run() {
        $this->actions();
    }

    /**
     * Perform update actions.
     *
     * @return void
     */
    abstract public function actions();
}
