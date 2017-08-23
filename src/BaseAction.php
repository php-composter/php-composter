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

use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Symfony\Component\Console;

/**
 * Abstract Class BaseAction.
 *
 * This class should be extended by each new action.
 *
 * @since   0.1.3
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class BaseAction
{

    const LOCALE       = 'en_US.UTF-8';
    const ENCODING_ENV = 'LC_ALL=' . self::LOCALE;
    const GIT_BINARY   = 'git';

    /**
     * Root folder of the package.
     *
     * @var string
     *
     * @since 0.1.3
     */
    protected $root;

    /**
     * Mirror folder of the package.
     *
     * @var string
     *
     * @since 0.3.0
     */
    protected $mirror;

    /**
     * Hook that was triggered.
     *
     * @var string
     *
     * @since 0.1.3
     */
    protected $hook;

    /**
     * Input/output interface.
     *
     * @var IOInterface
     *
     * @since 0.3.0
     */
    protected $io;

    /**
     * Instantiate a BaseAction object.
     *
     * @since 0.1.3
     *
     * @param string      $hook The name of the hook that was triggered.
     * @param string      $root Absolute path to the root folder of the package.
     * @param IOInterface $io   Optional. Input/Output interface implementation.
     */
    public function __construct($hook, $root, IOInterface $io = null)
    {
        $this->root = $root;
        $this->hook = $hook;
        setlocale(LC_CTYPE, static::LOCALE);
        $this->io = $io ?: $this->getDefaultConsoleIO();
    }

    /**
     * Get the default console input/output implementation.
     *
     * @since 0.1.3
     *
     * @return IOInterface A HelperSet instance
     */
    protected function getDefaultConsoleIO()
    {
        return new ConsoleIO(
            new Console\Input\ArgvInput(),
            new Console\Output\ConsoleOutput(),
            $this->getDefaultHelperSet()
        );
    }

    /**
     * Get the default helper set with the helpers that should always be available.
     *
     * @since 0.1.3
     *
     * @return Console\Helper\HelperSet A HelperSet instance
     */
    protected function getDefaultHelperSet()
    {
        return new Console\Helper\HelperSet(array(
            new Console\Helper\FormatterHelper(),
            new Console\Helper\DebugFormatterHelper(),
            new Console\Helper\ProcessHelper(),
            new Console\Helper\QuestionHelper(),
        ));
    }

    /**
     * Initialize the action.
     *
     * @since 0.1.3
     */
    public function init()
    {
        // Do nothing. Can be overridden by extending classes.
    }

    /**
     * Shut the action down.
     *
     * @since 0.1.3
     */
    public function shutdown()
    {
        // Do nothing. Can be overridden by extending classes.
    }

    /**
     * Destroy the BaseAction object again.
     *
     * @since 0.3.0
     */
    public function __destruct()
    {
        if (!empty($this->mirror)) {
            $filesystem = new Filesystem();
            $filesystem->removeDirectory($this->mirror);
        }
    }

    /**
     * Generate an error message and optionally halt further execution.
     *
     * @since 0.3.0
     *
     * @param string    $message  Error message to render.
     * @param int|false $exitCode Integer exit code, or false if execution should not be halted.
     */
    protected function error($message, $exitCode)
    {
        $this->io->writeError($message);
        false === $exitCode || exit($exitCode);
    }

    /**
     * Generate a success message and optionally halt further execution.
     *
     * @since 0.3.0
     *
     * @param string    $message  Success message to render.
     * @param int|false $exitCode Optional. Integer exit code, or false if execution should not be halted.
     *                            Defaults to 0.
     */
    protected function success($message, $exitCode = 0)
    {
        $this->io->write($message);
        false === $exitCode || exit($exitCode);
    }

    /**
     * Recursively iterate over folders and look for $pattern.
     *
     * @since 0.1.3
     *
     * @param string $pattern Pattern to look for.
     * @param int    $flags   Optional. Flags to PHP glob() function. Defaults to 0.
     *
     * @return mixed
     */
    protected function recursiveGlob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            // Avoid scanning vendor folder.
            if ($dir === $this->root . '/vendor') {
                continue;
            }

            $files = array_merge($files, $this->recursiveGlob($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }

    /**
     * Get the files that have been staged for the current commit.
     *
     * If the `$mirrorChanges` is set to `true`, the method will create a mirror of the staged changes in a temporary
     * folder, and return paths pointing to this temporary folder. Otherwise, file-based tools will run against the
     * current working tree, not the changes that are actually staged.
     *
     * @since 0.1.3
     *
     * @var string $pattern             Optional. Grep pattern to filter the staged files against.
     * @var bool   $mirrorStagedChanges Optional. Whether to create a file-based mirror of the staged changes.
     *                                  Defaults to `true`.
     * @return array
     * @throws \RuntimeException
     */
    protected function getStagedFiles($pattern = '', $mirrorStagedChanges = true)
    {
        $filter = empty($pattern)
            ? ''
            : " | grep {$pattern}";

        // Get the list of file names that are staged.
        $diffCommand = $this->gitCall('diff-index --name-only --diff-filter=ACMR', $this->getAgainst(), $filter);

        exec($diffCommand, $files, $return);

        // Unknown problem while fetching the index.
        if (Git::DIFF_INDEX_ERROR === $return) {
            throw new \RuntimeException('Fetching staged files returns an error');
        }

        // No files found.
        if (Git::DIFF_INDEX_NO_FILES_FOUND === $return) {
            return [];
        }

        // Filter out empty and NULL values.
        $files = array_filter($files);

        // Check if we want to compare against the actual staged content changes (instead of only the file names).
        if ($mirrorStagedChanges) {
            $this->mirror = "{$this->root}/.git/staged";
            $filesystem   = new Filesystem();
            $filesystem->emptyDirectory($this->mirror);

            // Checkout the current index with a folder prefix.
            $checkoutCommand = $this->gitCall(
                'checkout-index',
                '--prefix=' . escapeshellarg("{$this->mirror}/"),
                '-af'
            );

            exec($checkoutCommand, $output, $return);

            // Detect content differences, and replace the file from the temporary mirror as needed.
            array_walk($files, [$this, 'detectStagedChanges'], [$this->root, $this->mirror]);
        } else {
            // No staged content changes needed, just return the name of the staged files.
            array_walk($files, [$this, 'prependRoot'], $this->root);
        }

        return $files;
    }

    /**
     * Get the tree object to check against.
     *
     * @return string HEAD or hash representing empty/initial commit state.
     * @throws \RuntimeException
     */
    protected function getAgainst()
    {
        $command = $this->gitCall('rev-parse --verify --quiet', Git::HEAD);

        exec($command, $output, $return);

        if (Git::UNEXPECTED_ERROR === $return) {
            throw new \RuntimeException('This is not a valid git repository');
        }

        if (Git::REV_PARSE_ERROR === $return) {
            throw new \RuntimeException('Finding the HEAD commit hash returned an error');
        }

        // Check if we're on a semi-secret empty tree.
        if ($output) {
            return Git::HEAD;
        }

        // Initial commit: diff against an empty tree object.
        return Git::EMPTY_TREE_OBJECT_HASH;
    }

    /**
     * Return an escaped call to git based on an arbitrary number of arguments.
     *
     * @since 0.3.0
     *
     * @param array <string> ...$args Array of arguments to escape.
     *
     * @return string Escaped call to git.
     */
    protected function gitCall(...$args)
    {
        return sprintf(
            '%s %s %s %s',
            static::ENCODING_ENV,
            static::GIT_BINARY,
            "--git-dir={$this->root}/.git --work-tree={$this->root}",
            implode(' ', $args)
        );
    }

    /**
     * Prepend the repository root path.
     *
     * @param string $file  File name by reference
     * @param int    $index Index into the array.
     * @param string $root  Root folder.
     */
    protected function prependRoot(&$file, $index, $root)
    {
        $file = "{$root}/{$file}";
    }

    /**
     * Prepend the repository root path.
     *
     * @param string $file    File name by reference
     * @param int    $index   Index into the array.
     * @param array  $folders Root and mirror folder paths.
     */
    protected function detectStagedChanges(&$file, $index, $folders)
    {
        list($root, $mirror) = $folders;

        if ($this->filesEqual("{$root}/{$file}", "{$mirror}/{$file}")) {
            $file = "{$root}/{$file}";

            return;
        }

        $file = "{$mirror}/{$file}";
    }

    /**
     * Compare two files to see whether they are equal.
     *
     * Does incremental comparison to avoid loading big files entirely if not needed.
     *
     * @since 0.3.0
     *
     * @param string $fileA Path to the first file to compare.
     * @param string $fileB Path to the second file to compare.
     *
     * @return bool Whether the two files were equal.
     */
    protected function filesEqual($fileA, $fileB)
    {
        if (!is_file($fileA) || !is_file($fileB)) {
            return false;
        }

        if (!is_readable($fileA) || !is_readable($fileB)) {
            return false;
        }

        if (filesize($fileA) !== filesize($fileB)) {
            return false;
        }

        $fileResourceA = fopen($fileA, 'rb');
        $fileResourceB = fopen($fileB, 'rb');

        $equal = true;

        while ($equal && ($bufferA = fread($fileResourceA, 4096)) !== false) {
            $bufferB = fread($fileResourceB, 4096);
            $equal   = $bufferA !== $bufferB;
        }

        fclose($fileResourceA);
        fclose($fileResourceB);

        return $equal;
    }
}
