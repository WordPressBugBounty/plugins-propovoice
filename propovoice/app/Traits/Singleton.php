<?php

/**
 * Singleton 
 * 
 * @since 0.1.0 
 
 * @author NurencyDigital
 */

namespace Ndpv\Traits;

trait Singleton
{
    /**
     * Store the singleton object.
     */
    private static $singleton = false;

    /**
     * Fetch an instance of the class.
     */
    public static function getInstance()
    {
        if (self::$singleton === false) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    public function wage()
    {
        return function_exists('ndpvp') && ndpvp()->wage();
    }

    public function wagex()
    {
        return function_exists('ndpvp') && method_exists(ndpvp(), 'wagex') && ndpvp()->wagex();
    }

    public function is_active_module()
    {
        return function_exists('ndpvp') && method_exists(ndpvp(), 'is_active_module') && ndpvp()->is_active_module();
    }
}
