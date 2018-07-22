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
 * Class Hook.
 *
 * @since   0.3.0
 * @since   0.3.2 Turned into class to change const array into method.
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class Hook
{

    const APPLYPATCH_MSG     = 'applypatch-msg';
    const PRE_APPLYPATCH     = 'pre-applypatch';
    const POST_APPLYPATCH    = 'post-applypatch';
    const PRE_COMMIT         = 'pre-commit';
    const PREPARE_COMMIT_MSG = 'prepare-commit-msg';
    const COMMIT_MSG         = 'commit-msg';
    const POST_COMMIT        = 'post-commit';
    const PRE_REBASE         = 'pre-rebase';
    const POST_CHECKOUT      = 'post-checkout';
    const POST_MERGE         = 'post-merge';
    const POST_UPDATE        = 'post-update';
    const PRE_AUTO_GC        = 'pre-auto-gc';
    const POST_REWRITE       = 'post-rewrite';
    const PRE_PUSH           = 'pre-push';

    /**
     * Get the list of supported hooks as an array.
     *
     * @since 0.3.2
     *
     * @return array<string> Array of hook names.
     */
    public static function getSupportedHooks()
    {
        return [
            self::APPLYPATCH_MSG,
            self::PRE_APPLYPATCH,
            self::POST_APPLYPATCH,
            self::PRE_COMMIT,
            self::PREPARE_COMMIT_MSG,
            self::COMMIT_MSG,
            self::POST_COMMIT,
            self::PRE_REBASE,
            self::POST_CHECKOUT,
            self::POST_MERGE,
            self::POST_UPDATE,
            self::PRE_AUTO_GC,
            self::POST_REWRITE,
            self::PRE_PUSH,
        ];
    }
}
