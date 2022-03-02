<?php

namespace Jackiedo\Packager\Contracts;

use Jackiedo\Packager\Package;

/**
 * The package creator repository.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
interface CreatorRepository
{
    /**
     * Create package with a given storage path.
     *
     * @param Package      $package               The package instance
     * @param string       $storeAt               The path to directory used to store the package
     * @param string|null  $lowestLaravelVersion  Lowest Laravel thread version that the package supports
     *
     * @return bool
     */
    public function create(Package $package, $storeAt, $lowestLaravelVersion = null);
}
