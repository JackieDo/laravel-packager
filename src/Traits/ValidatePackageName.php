<?php

namespace Jackiedo\Packager\Traits;

use InvalidArgumentException;

/**
 * The ValidatePackageName trait.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
trait ValidatePackageName
{
    /**
     * Validate the package name.
     *
     * @param string $name
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    protected function validatePackageName($name)
    {
        if (!preg_match('/^[\w|\/]+$/', $name)) {
            throw new InvalidArgumentException('The package name can only contain letters, numbers, underscores, dashes and slashes.');
        }

        if (!preg_match('/^[\w-]+\/[\w-]+$/', $name)) {
            throw new InvalidArgumentException('The package name must be of form: <vendor>/<project>');
        }

        return true;
    }
}
