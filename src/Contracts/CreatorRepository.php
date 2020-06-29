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
     * @param object $package The package instance
     * @param string $storeAt The path to directory used to store the package
     *
     * @return bool
     */
    public function create(Package $package, $storeAt);
}
