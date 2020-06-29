<?php

namespace Jackiedo\Packager\Console\Commands;

use Exception;
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
class RegisterPackageCommand extends Command
{
    use ValidatePackageName;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'packager:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register an existing package to the application repositories.';

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
        } catch (Exception $exception) {
            $this->errorBlock($exception->getMessage());

            return false;
        }

        if (!$this->manager->exists($packageName)) {
            $this->errorBlock('This package does not exist.');

            return false;
        }

        $this->newLine();
        $this->write('Registering the package...', 'info');

        $result = $this->manager->register($packageName);

        if (!$result) {
            $this->errorBlock('The process encountered an error.');

            return false;
        }

        $this->comment(' OK');
        $this->successBlock('Your package has been registered successfully.');
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
}
