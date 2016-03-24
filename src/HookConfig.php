<?php
/**
 * Git Hooks Management through Composer.
 *
 * @package   PHPComposter\PHPComposter
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   MIT
 * @link      http://www.brightnucleus.com/
 * @copyright 2016 Alain Schlesser, Bright Nucleus
 */

namespace PHPComposter\PHPComposter;

class HookConfig
{

    /**
     * Internal storage of the configuration data.
     *
     * @var array
     *
     * @since 0.1.0
     */
    protected static $config = array();

    /**
     * Add an entry to the configuration data.
     *
     * @since 0.1.0
     *
     * @param string $hook     Name of the Git hook to add to.
     * @param int    $priority Optional. Priority of the hook. Defaults to 10.
     * @param string $method   Fully qualified method name to add.
     */
    public static function addEntry($hook, $method, $priority = 10)
    {
        static::$config[$hook][$priority][] = $method;
    }

    /**
     * Get the entries for a given Git hook.
     *
     * @since 0.1.0
     *
     * @param string $hook Git hook to retrieve the methods for.
     *
     * @return array Array of fully qualified method names. Empty array if none.
     */
    public static function getEntries($hook)
    {
        if (array_key_exists($hook, static::$config)) {
            return static::$config[$hook];
        }

        return array();
    }
}
