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

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use InvalidArgumentException;

/**
 * Class Installer.
 *
 * The Installer class tells Composer where to install each package of type `php-composter-action`.
 *
 * @since   0.1.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Installer extends LibraryInstaller
{

    const PREFIX = 'php-composter/php-composter-';
    const TYPE   = 'php-composter-action';

    /**
     * Get the installation path of the package.
     *
     * @since 0.1.0
     *
     * @param PackageInterface $package The package to install.
     *
     * @return string Relative installation path.
     * @throws InvalidArgumentException If the package name does not match the required pattern.
     */
    public function getInstallPath(PackageInterface $package)
    {
        return Paths::getPath('actions') . $this->getSuffix($package);
    }

    /**
     * Install the package.
     *
     * @since 0.1.0
     *
     * @param InstalledRepositoryInterface $repo    The repository from where the package was fetched.
     * @param PackageInterface             $package The package to install.
     *
     * @throws InvalidArgumentException If the package name does not match the required pattern.
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $path = $this->getInstallPath($package);
        if ($this->io->isVerbose()) {
            $this->io->write(sprintf(
                _('Symlinking PHP Composter action %1$s'),
                $path
            ), true);
        }
        parent::install($repo, $package);
        foreach ($package->getExtra() as $prioritizedHook => $method) {
            $array = explode('.', $prioritizedHook);
            if (count($array) > 1) {
                list($priority, $hook) = $array;
            } else {
                $hook     = $array[0];
                $priority = 10;
            }
            if ($this->io->isVeryVerbose()) {
                $this->io->write(sprintf(
                    _('Adding method "%1$s" to hook "%2$s" with priority %3$s'),
                    $method,
                    $hook,
                    $priority
                ), true);
            }
            HookConfig::addEntry($hook, $method, $priority);
        }
    }

    /**
     * Check whether the package is already installed.
     *
     * @todo  This should be made smarter to not always reinstall from scratch.
     *
     * @since 0.1.0
     *
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface             $package
     *
     * @return bool
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // Always reinstall all PHP Composter actions.
        return false;
    }

    /**
     * Whether the installer supports a given package type.
     *
     * @since 0.1.0
     *
     * @param $packageType
     *
     * @return bool
     */
    public function supports($packageType)
    {
        return self::TYPE === $packageType;
    }

    /**
     * Get the package name suffix.
     *
     * @since 0.1.0
     *
     * @param PackageInterface $package Package to inspect.
     *
     * @return string Suffix of the package name.
     * @throws InvalidArgumentException If the package name does not match the required pattern.
     */
    protected function getSuffix(PackageInterface $package)
    {
        $prefixLength = mb_strlen(self::PREFIX);
        $prefix       = mb_substr($package->getPrettyName(), 0, $prefixLength);

        if (self::PREFIX !== $prefix) {
            throw new InvalidArgumentException(sprintf(
                _('Unable to install PHP Composter action, actions '
                  . 'should always start their package name with '
                  . '"%1$s"'),
                self::PREFIX
            ));
        }

        return mb_substr($package->getPrettyName(), $prefixLength);
    }
}
