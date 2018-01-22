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
 * Class Paths.
 *
 * This static class generates and distributes all the paths used by PHP Composter.
 *
 * @since   0.1.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Paths
{

    const ACTIONS_FOLDER      = 'actions/';
    const BIN_FOLDER          = 'bin/';
    const COMPOSTER_FOLDER    = 'php-composter/';
    const COMPOSTER_PATH      = 'php-composter/php-composter/';
    const CONFIG              = 'config.php';
    const EXECUTABLE          = 'php-composter';
    const GIT_FOLDER          = '.git/';
    const GIT_TEMPLATE_FOLDER = 'includes/';
    const HOOKS_FOLDER        = 'hooks/';
    const COMPOSER_CONFIG     = 'composer.json';

    /**
     * Internal storage of all required paths.
     *
     * @var array
     *
     * @since 0.1.0
     */
    protected static $paths = array();

    /**
     * Get a specific path by key.
     *
     * @since 0.1.0
     *
     * @param string $key Key of the path to retrieve.
     *
     * @return string Path associated with the key. Empty string if not found.
     */
    public static function getPath($key)
    {
        if (empty(static::$paths)) {
            static::initPaths();
        }

        if (array_key_exists($key, static::$paths)) {
            return static::$paths[$key];
        }

        return '';
    }

    /**
     * Initialize the paths.
     *
     * @since 0.1.0
     */
    protected static function initPaths()
    {
        static::$paths['pwd']              = getcwd() . DIRECTORY_SEPARATOR;
        static::$paths['composer_config']  = static::$paths['pwd'] . self::COMPOSER_CONFIG;
        static::$paths['root_git']         = static::$paths['pwd'] . self::GIT_FOLDER;
        static::$paths['root_hooks']       = static::$paths['root_git'] . self::HOOKS_FOLDER;
        static::$paths['vendor_composter'] = static::$paths['pwd'] . self::getConfigKey('vendor-dir', 'vendor' ) . DIRECTORY_SEPARATOR . self::COMPOSTER_PATH;
        static::$paths['git_composter']    = static::$paths['root_git'] . self::COMPOSTER_FOLDER;
        static::$paths['git_script']       = static::$paths['vendor_composter'] . self::BIN_FOLDER . self::EXECUTABLE;
        static::$paths['actions']          = static::$paths['git_composter'] . self::ACTIONS_FOLDER;
        static::$paths['git_template']     = static::$paths['vendor_composter'] . self::GIT_TEMPLATE_FOLDER;
        static::$paths['root_template']    = static::$paths['git_composter'] . self::GIT_TEMPLATE_FOLDER;
        static::$paths['git_config']       = static::$paths['git_composter'] . self::CONFIG;
    }

    /**
     * Get the value for a specific "config" config key.
     *
     * @since 0.3.0
     *
     * @param string $key      Key to retrieve.
     * @param mixed  $fallback Optional. Fallback value to use if the key is not found.
     *
     * @return mixed
     */
    protected static function getConfigKey($key, $fallback = null)
    {
        $config = self::getConfigArray();
        $config  = array_key_exists('config', $config)
            ? $config['config']
            : [];

        return array_key_exists($key, $config)
            ? $config[$key]
            : $fallback;
    }

    /**
     * Get the Composer configuration.
     *
     * @since 0.3.0
     *
     * @return array
     */
    protected static function getConfigArray()
    {
        static $config = null;

        if (null === $config) {
            $config = [];
            if (!is_readable(self::getPath('composer_config'))) {
                return $config;
            }

            $composerFile = file_get_contents(self::getPath('composer_config'));
            if (false === $composerFile) {
                return $config;
            }

            $config = json_decode($composerFile, true);
        }

        return $config;
    }
}
