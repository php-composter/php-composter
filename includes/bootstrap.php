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

use PHPComposter\PHPComposter\Paths;

// Get the command-line arguments passed from the shell script.
global $argv;
$hook = $argv[1];
$root = $argv[2];

// Initialize Composer Autoloader.
if (file_exists($root . '/vendor/autoload.php')) {
    require_once $root . '/vendor/autoload.php';
}

// Read the configuration file.
$config = include Paths::getPath('git_config');

// Iterate over hook methods.
if (array_key_exists($hook, $config)) {

    $actions = $config[$hook];

    // Sort by priority.
    ksort($actions);

    // Launch each method.
    foreach ($actions as $calls) {
        foreach ($calls as $call) {

            // Make sure we could parse the call correctly.
            $array = explode('::', $call);
            if (count($array) !== 2) {
                throw new RuntimeException(
                    sprintf(
                        _('Configuration error in PHP Composter data, could not parse method "%1$s"'),
                        $call
                    )
                );
            }
            list($class, $method) = $array;

            // Instantiate a new action object and call its method.
            $object = new $class($hook, $root);
            $object->init();
            $object->$method();
            $object->shutdown();
            unset($object);
        }
    }
}
