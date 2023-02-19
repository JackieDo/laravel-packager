<?php

namespace Jackiedo\Packager;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * The package information.
 *
 * @package jackiedo\laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
class Package
{
    /**
     * The package information.
     *
     * @var array
     */
    protected $information = [];

    /**
     * Lock setting the package information.
     *
     * @var bool
     */
    protected $lock = false;

    /**
     * Create a new package instance.
     *
     * @param string $name The package name
     *
     * @return void
     */
    public function __construct($name)
    {
        list($vendor, $project) = explode('/', $name, 2);

        $this->information['name']    = $name;
        $this->information['vendor']  = $vendor;
        $this->information['project'] = $project;
    }

    /**
     * Dynamically property accesstor.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        $getter = Str::camel('get_' . $property);

        if (method_exists($this, $getter)) {
            return call_user_func_array([$this, $getter], []);
        }

        if (array_key_exists($property, $this->information)) {
            return $this->information[$property];
        }

        return $this->{$property};
    }

    /**
     * Set the package information.
     *
     * @param array|string $information The information of package
     * @param mixed        $value       The value want to set
     *
     * @return $this
     *
     * @throws \ErrorException
     */
    public function setInformation($information, $value = null)
    {
        if ($this->isLocked()) {
            throw new \ErrorException('Setting package information is locked.');
        }

        if ($information) {
            $information = is_array($information) ? $information : [$information => $value];
            $information = Arr::except($information, ['vendor', 'project', 'name']);

            foreach ($information as $info => $value) {
                $this->information[$info] = $this->formatInformation($info, $value);
            }
        }

        return $this;
    }

    /**
     * Determine whether setting information is locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->lock;
    }

    /**
     * Lock setting package information.
     *
     * @return $this
     */
    public function lock()
    {
        $this->lock = true;

        return $this;
    }

    /**
     * Get the package vendor.
     *
     * @return string
     */
    public function getVendor()
    {
        return Str::studly(Arr::get($this->information, 'vendor', 'unknown'));
    }

    /**
     * Get the slug case of the package vendor.
     *
     * @return string
     */
    public function getSlugVendor()
    {
        return Str::snake($this->getVendor(), '-');
    }

    /**
     * Get the snake case of the package vendor.
     *
     * @return string
     */
    public function getSnakeVendor()
    {
        return Str::snake($this->getVendor(), '_');
    }

    /**
     * Get the camel case of the package vendor.
     *
     * @return string
     */
    public function getCamelVendor()
    {
        return Str::camel($this->getVendor());
    }

    /**
     * Get the lower case of the package vendor.
     *
     * @return string
     */
    public function getLowerVendor()
    {
        return Str::lower($this->getVendor());
    }

    /**
     * Get the upper case of the package vendor.
     *
     * @return string
     */
    public function getUpperVendor()
    {
        return Str::upper($this->getVendor());
    }

    /**
     * Get the title case of the package vendor.
     *
     * @return string
     */
    public function getTitleVendor()
    {
        return Str::title($this->getVendor());
    }

    /**
     * Get the package project name.
     *
     * @return string
     */
    public function getProject()
    {
        return Str::studly(Arr::get($this->information, 'project', 'unknown'));
    }

    /**
     * Get the slug case of the package project name.
     *
     * @return string
     */
    public function getSlugProject()
    {
        return Str::snake($this->getProject(), '-');
    }

    /**
     * Get the snake case of the package project name.
     *
     * @return string
     */
    public function getSnakeProject()
    {
        return Str::snake($this->getProject(), '_');
    }

    /**
     * Get the camel case of the package project name.
     *
     * @return string
     */
    public function getCamelProject()
    {
        return Str::camel($this->getProject());
    }

    /**
     * Get the lower case of the package project name.
     *
     * @return string
     */
    public function getLowerProject()
    {
        return Str::lower($this->getProject());
    }

    /**
     * Get the upper case of the package project name.
     *
     * @return string
     */
    public function getUpperProject()
    {
        return Str::upper($this->getProject());
    }

    /**
     * Get the title case of the package project name.
     *
     * @return string
     */
    public function getTitleProject()
    {
        return Str::title($this->getProject());
    }

    /**
     * Get the namespace prefix of package.
     *
     * @return string
     */
    public function getNamespace()
    {
        return Arr::get($this->information, 'namespace', $this->getVendor() . '\\' . $this->getProject());
    }

    /**
     * Get the addslashed of the namespace of package.
     *
     * @return string
     */
    public function getAddslashedNamespace()
    {
        return addslashes($this->getNamespace());
    }

    /**
     * Get the author information of package.
     *
     * This information will be formatted as: Author Name <author_email@domain.com>
     *
     * @return string
     */
    public function getAuthor()
    {
        $authorName  = Arr::get($this->information, 'author_name');
        $authorEmail = Arr::get($this->information, 'author_email');
        $authorEmail = $authorEmail ? '<' . $authorEmail . '>' : null;

        return trim($authorName . ' ' . $authorEmail);
    }

    /**
     * Get original value of information.
     *
     * @param string $information The information need to get
     * @param mixed  $default     The value will be return if information does not exist
     *
     * @return mixed
     */
    public function getOriginal($information, $default = null)
    {
        if ($information) {
            return Arr::get($this->information, $information, $default);
        }

        return $default;
    }

    /**
     * Format information before set.
     *
     * @param string $info        The information need format
     * @param mixed  $value       The original of information
     * @param mixed  $information
     *
     * @return mixed
     */
    protected function formatInformation($information, $value)
    {
        if ('keywords' == $information) {
            return array_map('trim', explode(',', $value));
        }

        return $value;
    }
}
