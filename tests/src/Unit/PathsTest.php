<?php
/**
 * This file is part of the "PHP Composter" package.
 *
 * © 2016 Franz Josef Kaiser
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPComposter\Tests\Unit;

use PHPComposter\PHPComposter\Paths;
use PHPComposter\Tests\TestCase;

class PathsTest extends TestCase
{

    /**
     * Test whether getPath() returns matching strings.
     *
     * @since        0.3.0
     *
     * @dataProvider getPathDataProvider
     *
     * @param string $key            The key to fetch.
     * @param string $expectedFormat A RegEx that defines the path that should be returned.
     */
    public function testGetPathReturnsMatchingStrings($key, $expectedFormat)
    {
        $path = Paths::getPath($key);
        $this->assertInternalType('string', $path);
        $this->assertNotEmpty($path);
        $this->assertRegExp($expectedFormat, $path);
    }

    /**
     * Provide test data to testGetPathReturnsMatchingStrings().
     *
     * @since 0.3.0
     *
     * @return array Array of string couples with test data.
     */
    public function getPathDataProvider()
    {
        return [
            // $key, $expectedFormat
            ['pwd', '|(.*)/$|'],
            ['root_git', '|(.*)/.git/$|'],
            ['root_hooks', '|(.*)/.git/hooks/$|'],
            ['vendor_composter', '|(.*)/vendor/php-composter/php-composter/$|'],
            ['git_composter', '|(.*)/.git/php-composter/$|'],
            ['git_script', '|(.*)/php-composter$|'],
            ['actions', '|(.*)/.git/php-composter/actions/$|'],
            ['git_template', '|(.*)/vendor/php-composter/php-composter/includes/$|'],
            ['root_template', '|(.*)/.git/php-composter/includes/$|'],
            ['git_config', '|(.*)/.git/php-composter/config.php$|'],
        ];
    }
}
