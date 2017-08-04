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
     * Hook that was triggered.
     *
     * @var string
     *
     * @since 0.1.3
     */
    protected $hook;

    /**
     * Instantiate a BaseAction object.
     *
     * @since 0.1.3
     *
     * @param string $hook The name of the hook that was triggered.
     * @param string $root Absolute path to the root folder of the package.
     */
    public function __construct($hook, $root)
    {
        $this->root = $root;
        $this->hook = $hook;
        setlocale(LC_CTYPE, static::LOCALE);
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

        $command = $this->gitCall('diff-index --name-only --diff-filter=ACMR', $this->getAgainst(), $filter);

        exec($command, $files, $return);

        if (Git::DIFF_INDEX_ERROR === $return) {
            throw new \RuntimeException('Fetching staged files returns an error');
        }

        // No files found.
        if (Git::DIFF_INDEX_NO_FILES_FOUND === $return) {
            return [];
        }

        // Filter out empty and NULL values.
        $files = array_filter($files);

        array_walk(
            $files,
            [$this, 'prependRoot'],
            $this->root
        );

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
     * @param string $file File name by reference
     * @param int    $index
     * @param string $root
     */
    private function prependRoot(&$file, $index, $root)
    {
        $file = $root . DIRECTORY_SEPARATOR . $file;
    }
}
