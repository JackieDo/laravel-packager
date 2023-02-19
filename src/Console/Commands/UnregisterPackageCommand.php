<?php

namespace Jackiedo\Packager\Console\Commands;

use Jackiedo\Packager\Console\Command;
use Jackiedo\Packager\Traits\ValidatePackageName;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * The command to remove package.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
class UnregisterPackageCommand extends Command
{
    use ValidatePackageName;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'packager:unregister';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unregiter an existing package from application repositories.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $packageName = $this->argument('name');

        try {
            $this->validatePackageName($packageName);
        } catch (\Exception $exception) {
            $this->errorBlock($exception->getMessage());

            return false;
        }

        $installStatus = $this->manager->getInstallStatus($packageName);
        $installed     = $installStatus['installed'];
        $canUninstall  = 0 == count($installStatus['required_by']);

        if ($installed) {
            if (!$canUninstall) {
                $this->errorBlock('This package cannot be unregistered because it is required by another package.');

                return false;
            }

            if (!$this->option('uninstall')) {
                $this->errorBlock('This package has been installed into your application.');
                $this->comment('Please run the "composer remove ' . $packageName . '" command to uninstall first.');
                $this->comment('Or use the "--uninstall" argument in your packager:remove command.');

                return false;
            }
        }

        $confirmed = $this->confirm('Are you sure?');

        if ($confirmed) {
            if ($installed) {
                $this->write(' Uninstalling the package...', 'info');

                $uninstalled = $this->manager->uninstall($packageName);

                if (!$uninstalled) {
                    $this->errorBlock('The process encountered an error.');

                    return false;
                }

                $this->comment(' OK');
            }

            $this->write(' Unregistering the package...', 'info');

            $unregistered = $this->manager->unregister($packageName);

            if (!$unregistered) {
                $this->errorBlock('The process encountered an error.');

                return false;
            }

            $this->comment(' OK');
            $this->successBlock('Your package has been unregistered successfully.');

            return true;
        }

        $this->block('Your request has been canceled.', null, 'comment');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name (<vendor>/<project>) of the package.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['uninstall', 'u', InputOption::VALUE_NONE, 'Request to uninstall package before perform unregister.'],
        ];
    }
}
