<?php

namespace Jackiedo\Packager\Contracts;

use Jackiedo\Packager\Package;

/**
 * The package manager repository.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
interface ManagerRepository
{
    /**
     * Get all packages.
     *
     * @return array
     */
    public function all();

    /**
     * Create a package.
     *
     * @param Package     $package               The package instance
     * @param string|null $lowestLaravelVersion  Lowest Laravel thread version that the package supports
     *
     * @return bool
     */
    public function create(Package $package, $lowestLaravelVersion = null);

    /**
     * Remove a package.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function remove($packageName);

    /**
     * Register the package to application repositories.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function register($packageName);

    /**
     * Unregister the package from application repositories.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function unregister($packageName);

    /**
     * Install the package.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function install($packageName);

    /**
     * Uninstall the package.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function uninstall($packageName);

    /**
     * Determine if package exists.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function exists($packageName);

    /**
     * Determine if the package has been registered.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function isRegistered($packageName);

    /**
     * Determine if the package has been installed.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function isInstalled($packageName);
}
