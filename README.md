# Laravel Packager

[![Latest Stable Version](https://poser.pugx.org/jackiedo/laravel-packager/v)](//packagist.org/packages/jackiedo/laravel-packager)
[![Total Downloads](https://poser.pugx.org/jackiedo/laravel-packager/downloads)](//packagist.org/packages/jackiedo/laravel-packager)
[![Latest Unstable Version](https://poser.pugx.org/jackiedo/laravel-packager/v/unstable)](//packagist.org/packages/jackiedo/laravel-packager)
[![License](https://poser.pugx.org/jackiedo/laravel-packager/license)](//packagist.org/packages/jackiedo/laravel-packager)

This package is a CLI tool that helps you build a fully structured package for the Laravel application without spending a lot of time.

You do not need to struggle with the skeleton initialization for your package anymore. Instead, focus on writing the source code and letting the organization of the package structure for Laravel Packager.

![Laravel Packager Cover](https://user-images.githubusercontent.com/9862115/86195079-52561980-bb7a-11ea-98bf-7ef00292bc10.jpg)

## Features
* Build directory structure for package.
* Generate a standard composer.json file for package.
* Generate a standard Service Provider file for package.
* Generate some scaffold resources such as: Facade, Interface, Abstract, Trait, Exception, Controller, Middleware, Model, Artisan Command, Config, Migration, Language, View, Route, Helper...
* Lets install and use the created package as a local repository.

## Versions and compatibility
This package is compatible with versions of **Laravel 5.1 and above**. However, the scaffold resources generated from this package are compatible with versions of Laravel 5.0 and above.

## Overview
Look at one of the following topics to learn more about Laravel Packager

* [Installation](#installation)
* [Usage](#usage)
    - [Create a new package](#1-create-a-new-package)
    - [List all packages](#2-list-all-packages)
    - [Unregister an existing package](#3-unregister-an-existing-package)
    - [Register an existing package](#4-register-an-existing-package)
    - [Remove an existing package](#5-remove-an-existing-package)
* [Configuration](#configuration)
* [Other documentation](#other-documentation)

### Installation
You can install Laravel Packager through [Composer](https://getcomposer.org) with the steps below.

#### Require package
At the root of your application directory, run the following command:

```shell
$ composer require jackiedo/laravel-packager
```

**Note:** Since Laravel 5.5, [service providers and aliases are automatically registered](https://laravel.com/docs/5.5/packages#package-discovery), you don't need to do anything more. But if you are using Laravel 5.4 or earlier, you must perform one more step below.

#### Register service provider
Open `config/app.php`, and add a new line to the `providers` section:

```php
Jackiedo\Packager\PackagerServiceProvider::class,
```

### Usage
#### 1. Create a new package
**Usage**:

```shell
$ php artisan packager:new [options] [--] <name>
```

**Arguments and options**:

```
Arguments:
  name                               The name (<vendor>/<project>) of the package.

Options:
      --author-name[=AUTHOR-NAME]    Author name.
      --author-email[=AUTHOR-EMAIL]  Author email.
      --description[=DESCRIPTION]    Package description.
      --keywords[=KEYWORDS]          Package keywords.
      --license[=LICENSE]            License of package.
      --homepage[=HOMEPAGE]          Package homepage.
  -r, --resources                    Request to create package with advanced resources.
  -i, --install                      Request to install package after creation.
  -h, --help                         Display this help message
  -q, --quiet                        Do not output any message
  -V, --version                      Display this application version
      --ansi                         Force ANSI output
      --no-ansi                      Disable ANSI output
  -n, --no-interaction               Do not ask any interactive question
      --env[=ENV]                    The environment the command should run under
  -v|vv|vvv, --verbose               Increase the verbosity of messages: 1 for normal output,
                                     2 for more verbose output and 3 for debug
```

**Example**:

- Create the `jackiedo/first-demo` package with advanced resources:

```shell
$ php artisan packager:new jackiedo/first-demo --resources
```

- Create the `jackiedo/second-demo` package and install it after creation:

```shell
$ php artisan packager:new jackiedo/second-demo --install
```

**Note**:

- All packages will be placed in the `packages` directory at the base path of your Laravel application.
- If you do not use the `--install` option in the command, you can install your package later using the composer command `composer require your/project`. This is useful in case you want to develop complete source code before installing it.

#### 2. List all packages
**Usage**:

```shell
$ php artisan packager:list
```

#### 3. Unregister an existing package
By default, when a package is created, it will be registered to the repositories section in Laravel's `composer.json` file automatically. This allows you to install your package as a local repository. If for any purpose you want to cancel this registration, use the following command:

**Usage**:

```shell
$ php artisan packager:unregister [options] [--] <name>
```

**Arguments and options**:

```
Arguments:
  name                  The name (<vendor>/<project>) of the package.

Options:
  -u, --uninstall       Request to uninstall package before perform unregister.
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
      --env[=ENV]       The environment the command should run under
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output,
                        2 for more verbose output and 3 for debug
```

**Example**: Unregister the `jackiedo/first-demo` package

```shell
$ php artisan packager:unregister jackiedo/first-demo
```

**Note**:

- If the package was previously installed, you need run the command `composer remove your/project` to uninstall it first or use the `--uninstall` option in your `packager:unregister` command.
- Once you have unregistered, you cannot install the package until you register again.

#### 4. Register an existing package
After unregister an existing package out of repositories section of `composer.json` file, if you want to register it again, use the following command:

**Usage**:

```shell
$ php artisan packager:register <name>
```

**Arguments and options**:

```
Arguments:
  name                  The name (<vendor>/<project>) of the package.

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
      --env[=ENV]       The environment the command should run under
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output,
                        2 for more verbose output and 3 for debug
```

#### 5. Remove an existing package
**Usage**:

```shell
$ php artisan packager:remove [options] [--] <name>
```

**Arguments and options**:

```
Arguments:
  name                  The name (<vendor>/<project>) of the package.

Options:
  -u, --uninstall       Request to uninstall package before removing.
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
      --env[=ENV]       The environment the command should run under
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output,
                        2 for more verbose output and 3 for debug
```

**Example**:

```shell
$ php artisan packger:remove jackiedo/first-demo --uninstall
```

### Configuration
If you want to set up more advanced settings such as skeleton structure, suggestions ... you need to publish the configuration file using the `vendor:publish` command as follow:

```shell
$ php artisan vendor:publish --provider="Jackiedo\Packager\PackagerServiceProvider" --tag="config"
```

**Note**: Please read the instructions in the configuration file carefully before making any settings.

### Other documentation
For more documentation about package development, you can visit Official Laravel Documentation pages:

- [Laravel Package Development](https://laravel.com/docs/7.x/packages)
- [Laravel Service Provider](https://laravel.com/docs/7.x/providers)
- [Laravel Artisan Console](https://laravel.com/docs/7.x/artisan)

## License
[MIT](https://github.com/JackieDo/laravel-packager/blob/master/LICENSE) © Jackie Do