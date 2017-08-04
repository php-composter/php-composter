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

namespace PHPComposter\Tests;

use PHPComposter\PHPComposter\BaseAction;

/**
 * Class TestProxyAction.
 *
 * This class only forwards method calls, as a convenience to test the base functionality of the BaseAction class.
 *
 * @since   0.3.0
 *
 * @package PHPComposter\Tests
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class TestProxyAction extends BaseAction
{

    /**
     * Forward a call to a specific method.
     *
     * @since 0.3.0
     *
     * @param string $method  Method name to call.
     * @param array  ...$args Array of arguments to use.
     */
    public function callMethod($method, ...$args)
    {
        return $this->$method(...$args);
    }
}
