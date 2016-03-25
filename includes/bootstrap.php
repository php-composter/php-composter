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

    $array = $config[$hook];

    // Sort by priority.
    ksort($array);

    // Launch each method.
    foreach ($array as $methods) {
        foreach ($methods as $method) {
            $method($hook, $root);
        }
    }
}
