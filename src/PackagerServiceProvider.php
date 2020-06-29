<?php

namespace Jackiedo\Packager;

use Illuminate\Support\ServiceProvider;
use Jackiedo\Packager\Console\Commands\ListPackageCommand;
use Jackiedo\Packager\Console\Commands\NewPackageCommand;
use Jackiedo\Packager\Console\Commands\RegisterPackageCommand;
use Jackiedo\Packager\Console\Commands\RemovePackageCommand;
use Jackiedo\Packager\Console\Commands\UnregisterPackageCommand;

/**
 * The Service Provider.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
class PackagerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Path to the package's configuration file.
     *
     * @var string
     */
    protected $packageConfig = __DIR__ . '/config/config.php';

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        ListPackageCommand::class       => 'command.packager.list',
        NewPackageCommand::class        => 'command.packager.new',
        RemovePackageCommand::class     => 'command.packager.remove',
        RegisterPackageCommand::class   => 'command.packager.register',
        UnregisterPackageCommand::class => 'command.packager.unregister',
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->packageConfig => config_path('packager.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->packageConfig, 'packager');

        $this->registerCreator();

        $this->registerManager();

        $this->registerCommands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge([
            'packager.creator',
            'packager.manager',
        ], array_values($this->commands));
    }

    /**
     * Register the package creator.
     *
     * @return void
     */
    protected function registerCreator()
    {
        $this->app->singleton('packager.creator', function ($app) {
            return new PackageCreator($app['config'], $app['files']);
        });
    }

    /**
     * Register the package manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('packager.manager', function ($app) {
            return new PackageManager($app['config'], $app['files'], $app['packager.creator']);
        });
    }

    /**
     * Register the given commands.
     *
     * @return void
     */
    protected function registerCommands(array $commands)
    {
        foreach ($commands as $className => $appName) {
            $this->app->singleton($appName, function ($app) use ($className) {
                return new $className($app['config'], $app['packager.manager']);
            });
        }

        $this->commands(array_values($commands));
    }
}
