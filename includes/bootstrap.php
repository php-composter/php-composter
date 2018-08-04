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

use Exception;
use LogicException;

// Get the command-line arguments passed from the shell script.
global $argv;
$arguments = $argv;

if (count($arguments) < 3) {
    exit("The PHP Composter bootstrap file was called in an unusual way, skipping git hooks.\n");
}

$bootstrapPath = array_shift($arguments);
$hook          = array_shift($arguments);
$root          = array_shift($arguments);

// Initialize Composer Autoloader.
if (!is_readable($root . '/vendor/autoload.php')) {
    exit("PHP Composter cannot access the Composer autoloader, skipping git hooks.\n");
}
require_once $root . '/vendor/autoload.php';

// Read the configuration file.
$config_path = Paths::getPath('git_config');
if (!is_readable($config_path)) {
    exit("PHP Composter cannot access its configuration file, skipping git hooks.\n");
}
$config = include $config_path;

// Make sure we have hooks to iterate over.
if (!array_key_exists($hook, $config)) {
    throw new LogicException(
        sprintf(
            'Configuration error in PHP Composter data, the hook "%1$s" is missing.',
            $hook
        )
    );
}

$actions = $config[$hook];

// Launch each method.
foreach ($actions as $calls) {
    foreach ($calls as $call) {

        // Make sure we could parse the call correctly.
        $array = explode('::', $call);
        if (count($array) !== 2) {
            echo "Configuration error in PHP Composter data, could not parse method '{$call}'.\n";
            break;
        }
        list($class, $method) = $array;

        if (!class_exists($class)) {
            echo "PHP Composter cannot instantiate class '{$class}', skipping.\n";
            break;
        }

        // Instantiate a new action object and call its method.
        /** @var BaseAction $object */
        $object = new $class($hook, $root);

        if (method_exists($object, 'init')) {
            $object->init();
        }

        try {
            call_user_func_array([$object, $method], $arguments);
        } catch (Exception $exception) {
            echo "PHP Composter cannot call '{$method}' on object of class '{$class}', skipping.\n";
            break;
        }

        if (method_exists($object, 'shutdown')) {
            $object->shutdown();
        }

        unset($object);
    }
}
