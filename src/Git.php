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
 * Interface Git.
 *
 * @since   0.3.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
interface Git
{

    // Object references.
    const HEAD                   = 'HEAD';
    const EMPTY_TREE_OBJECT_HASH = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';

    // General exit codes.
    const SUCCESS          = 0;
    const UNEXPECTED_ERROR = 128;

    // Exit codes for `diff index` operations.
    const DIFF_INDEX_NO_FILES_FOUND = 1;
    const DIFF_INDEX_ERROR          = 2;

    // Exit codes for `rev-parse` operations.
    const REV_PARSE_ERROR = 2;
}
