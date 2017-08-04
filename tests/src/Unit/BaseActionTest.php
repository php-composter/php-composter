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

use PHPComposter\PHPComposter\BaseAction;
use PHPComposter\PHPComposter\Git;
use PHPComposter\PHPComposter\Hook;
use PHPComposter\Tests\TestCase;
use PHPComposter\Tests\TestProxyAction;

/**
 * Class BaseActionTest.
 *
 * @since   0.3.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class BaseActionTest extends TestCase
{

    private $fixtures;

    public function setUp()
    {
        $this->fixtures = dirname(dirname(__DIR__)) . '/fixtures';
    }

    public function tearDown()
    {
        $folder = escapeshellarg("{$this->fixtures}/folder1");
        exec(BaseAction::ENCODING_ENV . " rm -rf {$folder}/.git");
        exec(BaseAction::ENCODING_ENV . " rm -f {$folder}/test.txt");
    }

    public function testBaseClassCanBeInstantiated()
    {
        $object = new BaseAction(Hook::PRE_COMMIT, $this->fixtures);
        $this->assertInstanceOf(BaseAction::class, $object);
    }

    public function testRecursiveGlobWorksAsExpected()
    {
        $action = new TestProxyAction(Hook::PRE_COMMIT, $this->fixtures);
        $files  = $action->callMethod('recursiveGlob', $this->fixtures . '/test2*.php');
        $this->assertEquals([
            $this->fixtures . '/folder2/test2.php',
            $this->fixtures . '/folder2/folder2a/test2a.php',
            $this->fixtures . '/folder2/folder2b/test2b.php',
        ], $files);
    }

    public function testGitCallProducesValidOutput()
    {
        $folder  = escapeshellarg($this->fixtures);
        $action  = new TestProxyAction(Hook::PRE_COMMIT, $folder);
        $command = $action->callMethod('gitCall', 'positional arguments', '--parameter', 21 * 2);
        $this->assertEquals(
            BaseAction::ENCODING_ENV . ' ' . BaseAction::GIT_BINARY
            . " --git-dir={$folder}/.git --work-tree={$folder} positional arguments --parameter 42",
            $command
        );
    }

    public function testGetAgainstThrowsExceptionOnNonGitFolder()
    {
        $action = new TestProxyAction(Hook::PRE_COMMIT, $this->fixtures);
        $this->expectException(\RuntimeException::class);
        $action->callMethod('getAgainst');
    }

    public function testGetAgainstReturnsEmptyTreeObjectHashOnEmptyGitRepo()
    {
        $folder = escapeshellarg("{$this->fixtures}/folder1");
        $action = new TestProxyAction(Hook::PRE_COMMIT, $folder);
        exec(BaseAction::ENCODING_ENV . ' ' . BaseAction::GIT_BINARY
             . " --git-dir={$folder}/.git --work-tree={$folder} init");
        $against = $action->callMethod('getAgainst');
        $this->assertEquals(Git::EMPTY_TREE_OBJECT_HASH, $against);
    }

    public function testGetAgainstReturnsHeadOnLatestCommit()
    {
        $folder = escapeshellarg("{$this->fixtures}/folder1");
        $action = new TestProxyAction(Hook::PRE_COMMIT, $folder);
        exec(BaseAction::ENCODING_ENV . ' ' . BaseAction::GIT_BINARY
             . " --git-dir={$folder}/.git --work-tree={$folder} init");
        exec(BaseAction::ENCODING_ENV
             . " cd {$folder} && touch test.txt && git add test.txt && git commit -m 'Initial Commit.'");
        $against = $action->callMethod('getAgainst');
        $this->assertEquals(Git::HEAD, $against);
    }

    public function testGetStagedFilesCanReturnTheWorkingTree()
    {
        $folder = escapeshellarg("{$this->fixtures}/folder1");
        $action = new TestProxyAction(Hook::PRE_COMMIT, "{$this->fixtures}/folder1");
        exec(BaseAction::ENCODING_ENV . ' ' . BaseAction::GIT_BINARY
             . " --git-dir={$folder}/.git --work-tree={$folder} init");
        exec(BaseAction::ENCODING_ENV
             . " cd {$folder} && touch test.txt && echo 12345 > test.txt && git add test.txt && git commit -m 'Initial Commit.'");
        exec(BaseAction::ENCODING_ENV
             . " cd {$folder} && echo staged > test.txt && git add test.txt && echo working > test.txt");
        $stagedFiles = $action->callMethod('getStagedFiles', null, $mirrorStagedChanges = false);
        $this->assertCount(1, $stagedFiles);
        $file = array_pop($stagedFiles);
        $this->assertStringEndsWith('/test.txt', $file);
        $this->assertStringEqualsFile($file, 'working' . PHP_EOL);
    }

    public function testGetStagedFilesCanReturnTheStagedChanges()
    {
        $folder = escapeshellarg("{$this->fixtures}/folder1");
        $action = new TestProxyAction(Hook::PRE_COMMIT, "{$this->fixtures}/folder1");
        exec(BaseAction::ENCODING_ENV . ' ' . BaseAction::GIT_BINARY
             . " --git-dir={$folder}/.git --work-tree={$folder} init");
        exec(BaseAction::ENCODING_ENV
             . " cd {$folder} && touch test.txt && echo 12345 > test.txt && git add test.txt && git commit -m 'Initial Commit.'");
        exec(BaseAction::ENCODING_ENV
             . " cd {$folder} && echo staged > test.txt && git add test.txt && echo working > test.txt");
        $stagedFiles = $action->callMethod('getStagedFiles', null, $mirrorStagedChanges = true);
        $this->assertCount(1, $stagedFiles);
        $file = array_pop($stagedFiles);
        $this->assertStringEndsWith('/test.txt', $file);
        $this->assertStringEqualsFile($file, 'staged' . PHP_EOL);
    }
}
