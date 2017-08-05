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

namespace PHPComposter\Tests\Unit;

use PHPComposter\PHPComposter\Hook;
use PHPComposter\PHPComposter\HookConfig;
use PHPComposter\Tests\TestCase;

/**
 * Class HookConfigTest.
 *
 * @since   0.3.0
 *
 * @package PHPComposter\Tests\Unit
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class HookConfigTest extends TestCase
{

    /**
     * Test whether entries can be added to HookConfig.
     *
     * @since 0.3.0
     */
    public function testAddingEntries()
    {
        $this->assertEquals(HookConfig::getEntries(Hook::PRE_COMMIT), []);
        HookConfig::addEntry(Hook::PRE_COMMIT, 'testMethodA', 15);
        $this->assertEquals(HookConfig::getEntries(Hook::PRE_COMMIT), [15 => ['testMethodA']]);
        HookConfig::addEntry(Hook::PRE_COMMIT, 'testMethodB', 5);
        HookConfig::addEntry(Hook::PRE_COMMIT, 'testMethodC', 5);
        $this->assertEquals(HookConfig::getEntries(Hook::PRE_COMMIT), [
            5 => ['testMethodB', 'testMethodC'],
            15 => ['testMethodA']
        ]);
        $this->assertEquals(HookConfig::getEntries(Hook::POST_COMMIT), []);
    }
}
