<?php

/**
 * Singleton
 *
 * @since 0.1.0
 * @author NurencyDigital
 */

namespace Ndpv\Traits;

trait Singleton {

    /**
     * Store the singleton object.
     */
    private static $singleton = false;

    /**
     * Fetch an instance of the class.
     */
    public static function init() {
        if ( self::$singleton === false ) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }
}
