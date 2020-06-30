# Laravel Packager
This package is a CLI tool that helps you build a fully structured package for the Laravel application without spending a lot of time.

You do not need to struggle with the skeleton initialization for your package anymore. Instead, focus on writing the source code and letting the organization of the package structure for Laravel Packager.

# Features
* Build directory structure for package.
* Generate a standard composer.json file for package.
* Generate a standard Service Provider file for package.
* Generate some scaffold resources file, such as:
    * Facade files
    * Interface files
    * Abstract files
    * Exception files
    * Controller files
    * Middleware files
    * Model files
    * Artisan CLI files
    * Configuration file
    * Migration files
    * Language files
    * View files
    * Route file
    * Helper file
    * ...

# Versions and compatibility
This package is compatible with versions of **Laravel 5.1 and above**. However, scaffold resources files generated from this package are compatible with versions of Laravel 5.0 and above.

# Overview
Look at one of the following topics to learn more about Laravel Packager

* [Installation](#installation)
* [Usage](#usage)
    - [Create a new package](#create-a-new-package)
    - [List all packages](#list-all-packages)
    - [Unregister an existing package](#unregister-an-existing-package)
    - [Register an existing package](#register-an-existing-package)
    - [Remove an existing package](#remove-an-existing-package)
* [Configuration](#configuration)
* [Other documentation](#other-documentation)

## Installation
You can install Laravel Packager through [Composer](https://getcomposer.org) with the steps below.

### Require package
At the root of your application directory, run the following command:

```shell
$ composer require jackiedo/laravel-packager
```

**Note:** Since Laravel 5.5, [service providers and aliases are automatically registered](https://laravel.com/docs/5.5/packages#package-discovery), you don't need to do anything more. But if you are using Laravel 5.4 or earlier, you must perform one more step below.

### Register service provider
Open `config/app.php`, and add a new line to the `providers` section:

```php
Jackiedo\Packager\PackagerServiceProvider::class,
```

## Usage
### Create a new package
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
- If you do not use the `--install` option in command, you can install your package after creation using the composer command `composer require your/project`. This is useful in case you want to develop complete source code before installing it.

### List all packages
**Usage**:

```shell
$ php artisan packager:list
```

### Unregister an existing package
By default, when a package is created, it will be registered to the repositories section in Laravel's `composer.json` file automatically. This allows you to install your package as a local repository. If you want to cancel this registration, use the following command:

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

- If the package is installed after creation, you need use the `--uninstall` option in your `packager:unregister` command or run the command `composer remove your/project` to uninstall it before unregister.
- Once you have unregistered, you cannot install the package until you register again.

### Register an existing package
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

### Remove an existing package
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

## Configuration
If you want to set up more advanced settings such as skeleton structure, suggestions ... you need to publish the configuration file using the `vendor:publish` command as follow:

```shell
$ php artisan vendor:publish --provider="Jackiedo\Packager\PackagerServiceProvider" --tag="config"
```

**Note**: Please read the instructions in the configuration file carefully before making any settings.

## Other documentation
For more documentation about package development, you can visit Official Laravel Documentation pages:

- [Laravel Package Development](https://laravel.com/docs/7.x/packages)
- [Laravel Service Provider](https://laravel.com/docs/7.x/providers)
- [Laravel Artisan Console](https://laravel.com/docs/7.x/artisan)

# License
[MIT](https://github.com/JackieDo/laravel-packager/blob/master/LICENSE) © Jackie Do