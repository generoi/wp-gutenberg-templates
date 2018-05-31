<?php

namespace GeneroWP\BlockBoilerplate\Common;

trait Singleton
{
    /**
     * @var self
     */
    protected static $instance = null;

    /**
     * @return self
     */
    final public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prevent the instance from being cloned
     *
     * @return void
     */
    final private function __clone()
    {
    }

    /**
     * Prevent from being unserialized
     *
     * @return void
     */
    final private function __wakeup()
    {
    }
}
