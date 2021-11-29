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

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;

/**
 * Class Plugin.
 *
 * This main class activates and sets up the PHP Composter system within the package's .git folder.
 *
 * @since   0.1.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * The name of the current package.
     * Used in error output.
     *
     * @var string
     */
    const PACKAGE_NAME = 'php-composter/php-composter';

    /**
     * Instance of the IO interface.
     *
     * @var IOInterface
     *
     * @since 0.1.0
     */
    protected static $io;

    /**
     * Get the event subscriber configuration for this plugin.
     *
     * @return array<string,string> The events to listen to, and their associated handlers.
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'persistConfig',
            ScriptEvents::POST_UPDATE_CMD  => 'persistConfig',
        ];
    }

    /**
     * Persist the stored configuration.
     *
     * @since 0.1.0
     *
     * @param Event $event Event that was triggered.
     */
    public static function persistConfig(Event $event)
    {
        $filesystem = new Filesystem();
        $path       = Paths::getPath('git_composter');
        $filesystem->ensureDirectoryExists($path);
        file_put_contents(Paths::getPath('git_config'), static::getConfig());
    }

    /**
     * Generate the config file.
     *
     * @since 0.1.0
     *
     * @return string Generated Config file.
     */
    public static function getConfig()
    {
        $output = '<?php' . PHP_EOL;
        $output .= '// PHP Composter configuration file.' . PHP_EOL;
        $output .= '// Do not edit, this file is generated automatically.' . PHP_EOL;
        $output .= '// Timestamp: ' . date('Y/m/d H:m:s') . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'return array(' . PHP_EOL;

        foreach (Hook::getSupportedHooks() as $hook) {
            $entries = HookConfig::getEntries($hook);
            $output  .= '    \'' . $hook . '\' => array(' . PHP_EOL;
            foreach ($entries as $priority => $methods) {
                $output .= '        ' . $priority . ' => array(' . PHP_EOL;
                foreach ($methods as $method) {
                    $output .= '            \'' . $method . '\',' . PHP_EOL;
                }
                $output .= '        ),' . PHP_EOL;
            }
            $output .= '    ),' . PHP_EOL;
        }

        $output .= ');' . PHP_EOL;

        return $output;
    }

    /**
     * Activate the Composer plugin.
     *
     * @since 0.1.0
     *
     * @param Composer    $composer Reference to the Composer instance.
     * @param IOInterface $io       Reference to the IO interface.
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        static::$io = $io;
        if (static::$io->isVerbose()) {
            static::$io->write('Activating PHP Composter plugin', true);
        }

        $installer = new Installer(static::$io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);

        $filesystem = new Filesystem();
        $this->cleanUp($filesystem);
        $this->linkBootstrapFiles($filesystem);
        $this->createGitHooks($filesystem);
    }

    /**
     * Remove any hooks from Composer.
     *
     * This will be called when a plugin is deactivated before being
     * uninstalled, but also before it gets upgraded to a new version
     * so the old one can be deactivated and the new one activated.
     *
     * @since 0.5.0
     *
     * @param Composer    $composer Reference to the Composer instance.
     * @param IOInterface $io       Reference to the IO interface.
     * @return void
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        static::$io = $io;
        if (static::$io->isVerbose()) {
            static::$io->write('Deactivating PHP Composter plugin', true);
        }

        $installer = $composer->getInstallationManager()->getInstaller(Installer::TYPE);
        $composer->getInstallationManager()->removeInstaller($installer);

        $filesystem = new Filesystem();
        $this->cleanUp($filesystem);
    }

    /**
     * Prepare the plugin to be uninstalled.
     *
     * This will be called after deactivate.
     *
     * @param Composer    $composer Reference to the Composer instance.
     * @param IOInterface $io       Reference to the IO interface.
     * @return void
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Nothing needs to be done here.
    }

    /**
     * Clean up previous installation.
     *
     * @since 0.1.0
     *
     * @param Filesystem $filesystem Reference to the Filesystem instance.
     */
    protected function cleanUp(Filesystem $filesystem)
    {
        $composterPath = Paths::getPath('git_composter');
        if (static::$io->isVeryVerbose()) {
            static::$io->write(sprintf(
                'Removing previous PHP Composter actions at %1$s',
                $composterPath
            ), true);
        }
        $filesystem->emptyDirectory($composterPath, true);

        $composterTemplate = Paths::getPath('root_template');
        if (static::$io->isVeryVerbose()) {
            static::$io->write(sprintf(
                'Removing previous PHP Composter code at %1$s',
                $composterTemplate
            ), true);
        }
        $filesystem->emptyDirectory($composterTemplate, true);
    }

    /**
     * Symlink the bootstrapping code into the .git folder.
     *
     * @since 0.1.0
     *
     * @param Filesystem $filesystem Reference to the Filesystem instance.
     */
    protected function linkBootstrapFiles(Filesystem $filesystem)
    {
        $rootTemplate      = Paths::getPath('root_template');
        $composterTemplate = Paths::getPath('git_template');

        $files = [
            'bootstrap.php',
        ];

        $filesystem->ensureDirectoryExists($rootTemplate);

        foreach ($files as $file) {
            if (static::$io->isVeryVerbose()) {
                static::$io->write(sprintf(
                    'Symlinking %1$s to %2$s',
                    $rootTemplate . $file,
                    $composterTemplate . $file
                ));
            }
            $this->createRelativeSymlink($filesystem, $composterTemplate . $file, $rootTemplate . $file);
        }
    }

    /**
     * Symlink each known Git hook to the PHP Composter bootstrapping script.
     *
     * @since 0.1.0
     *
     * @param Filesystem $filesystem Reference to the Filesystem instance.
     */
    protected function createGitHooks(Filesystem $filesystem)
    {

        $hooksPath     = Paths::getPath('root_hooks');
        $gitScriptPath = Paths::getPath('git_script');

        $filesystem->ensureDirectoryExists($hooksPath);

        foreach (Hook::getSupportedHooks() as $githook) {
            $hookPath = $hooksPath . $githook;
            if (is_link($hookPath)) {
                continue;
            }
            if (static::$io->isDebug()) {
                static::$io->write(sprintf(
                    'Symlinking %1$s to %2$s',
                    $hookPath,
                    $gitScriptPath
                ));
            }
            $this->createRelativeSymlink($filesystem, $gitScriptPath, $hookPath);
        }
    }

    /**
     * Tries to create a relative symlink with the filesystem. If this fails, try an absolute symlink.
     *
     * @throws \RuntimeException When also the absolute symlink creation fails.
     *
     * @param Filesystem $filesystem
     * @param            $target
     * @param            $link
     */
    protected function createRelativeSymlink(Filesystem $filesystem, $target, $link)
    {
        if (!$filesystem->relativeSymlink($target, $link)) {
            static::$io->write(
                'Unable to create relative symlink, try absolute symlink.',
                true,
                IOInterface::VERBOSE
            );

            try {
                symlink($filesystem->normalizePath($target), $filesystem->normalizePath($link));
            } catch (\ErrorException $e) {
                // Generate a more explanatory exception instead of the standard symlink messages.
                $explanatoryException = new \RuntimeException(sprintf(
                    '%3$s: Failed to create absolute symlink %1$s to %2$s',
                    $filesystem->normalizePath($link),
                    $filesystem->normalizePath($target),
                    static::PACKAGE_NAME
                ), 0, $e);

                // If we are on windows and the code of the ErrorException is 1314, you do not have sufficient privilege
                // to perform a symlink.
                if ($e->getMessage() === 'symlink(): Cannot create symlink, error code(1314)'
                    && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Inform the user that it is a privilege issue.
                    throw new \RuntimeException(sprintf(
                        '%1$s Failed to create symbolic link: ' .
                        'You do not have sufficient privilege to perform this operation. ' .
                        'Please run this command as administrator.',
                        static::PACKAGE_NAME
                    ), 0, $explanatoryException);
                } elseif (file_exists($link)) {
                    // File already exists, issue a warning.
                    static::$io->isVeryVerbose() && static::$io->write(sprintf(
                        '%1$s: Cannot create symlink at %2$s. File already exists.',
                        static::PACKAGE_NAME,
                        $filesystem->normalizePath($link)
                    ));
                } else {
                    throw $explanatoryException;
                }
            }
        }
    }
}
