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

use RuntimeException;

// Get the command-line arguments passed from the shell script.
global $argv;
$arguments     = $argv;
$bootstrapPath = array_shift($arguments);
$hook          = array_shift($arguments);
$root          = array_shift($arguments);

// Initialize Composer Autoloader.
if (file_exists($root . '/autoload.php')) {
    require_once $root . '/autoload.php';
}

// Read the configuration file.
$config = include Paths::getPath('git_config');

// Iterate over hook methods.
if (array_key_exists($hook, $config)) {

    $actions = $config[$hook];

    // Launch each method.
    foreach ($actions as $calls) {
        foreach ($calls as $call) {

            // Make sure we could parse the call correctly.
            $array = explode('::', $call);
            if (count($array) !== 2) {
                throw new RuntimeException(
                    sprintf(
                        'Configuration error in PHP Composter data, could not parse method "%1$s"',
                        $call
                    )
                );
            }
            list($class, $method) = $array;

            // Instantiate a new action object and call its method.
            /** @var BaseAction $object */
            $object = new $class($hook, $root);
            $object->init();
            $object->$method(...$arguments);
            $object->shutdown();
            unset($object);
        }
    }
}
