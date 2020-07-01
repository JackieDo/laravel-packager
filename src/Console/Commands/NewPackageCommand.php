<?php

namespace Jackiedo\Packager\Console\Commands;

use Exception;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Jackiedo\Packager\Console\Command;
use Jackiedo\Packager\Package;
use Jackiedo\Packager\PackageManager;
use Jackiedo\Packager\Traits\ValidatePackageName;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * The command to create package.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
class NewPackageCommand extends Command
{
    use ValidatePackageName;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'packager:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package with the given name.';

    /**
     * The package instance.
     *
     * @var Jackiedo\Packager\Package
     */
    protected $package;

    /**
     * Available resources can genergate.
     *
     * @var array
     */
    protected $builtinResources = [
        // Resources using namespace
        'facade',
        'interface',
        'abstract',
        'trait',
        'exception',
        'controller',
        'middleware',
        'model',
        'command',

        // Resources do not use namespace
        'config',
        'migration',
        'assets',
        'lang',
        'view',
        'route',
        'helper',
    ];

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

        if ($this->manager->exists($packageName)) {
            $this->errorBlock('This package already exists.');

            return false;
        }

        $this->package = new Package($packageName);

        $this->setComposerInfo();
        $this->setResources();

        $created = $this->createPackage();

        if (!$created) {
            $this->errorBlock('The process encountered an error.');

            return false;
        }

        if ($this->option('install')) {
            $installed = $this->installPackage();

            if (!$installed) {
                $this->warningBlock('Your package was created successfully but there was an error during the installation process.');
            } else {
                $this->successBlock('Your package has been created and installed successfully.');
            }

            return true;
        }

        $this->successBlock('Your package has been created successfully.');
        $this->whiteBlock('You can install your package using the command: "composer require ' . $this->package->name . '"');
    }

    /**
     * Set composer information for package.
     *
     * @return $this
     */
    protected function setComposerInfo()
    {
        $this->section('Set Package Information.');
        $this->line('Please provide the following package information.');

        $authorName  = $this->option('author-name');
        $authorEmail = $this->option('author-email');
        $description = $this->option('description');
        $keywords    = $this->option('keywords');
        $license     = $this->option('license');
        $homepage    = $this->option('homepage');

        if (empty($authorName)) {
            $suggestion = $this->config->get('packager.suggestions.author_name') ?: Str::title(str_replace('-', ' ', $this->package->slug_vendor));
            $authorName = $this->ask('Author name', $suggestion);
        }

        if (empty($authorEmail)) {
            $suggestion  = $this->config->get('packager.suggestions.author_email') ?: $this->package->lower_vendor . '@' . $this->package->lower_project . '.com';
            $authorEmail = $this->ask('Author email', $suggestion);
        }

        if (empty($description)) {
            $suggestion  = 'The ' . Str::title(str_replace('-', ' ', $this->package->slug_project));
            $description = $this->ask('Package description', $suggestion);
        }

        if (empty($keywords)) {
            $suggestion = 'example, key, words';
            $keywords   = $this->ask('Package keywords', $suggestion);
        }

        if (empty($license)) {
            $suggestion = $this->config->get('packager.suggestions.license') ?: 'MIT';
            $license    = $this->ask('Package license', $suggestion);
        }

        if (empty($homepage)) {
            $suggestion = 'https://' . $this->package->lower_project . '.com';
            $homepage   = $this->ask('Package homepage', $suggestion);
        }

        $namespace = $this->ask('Namespace prefix of package', $this->package->vendor . '\\' . $this->package->project, function ($answer) {
            $answer = trim($answer, '\\');

            if (empty($answer)) {
                throw new InvalidArgumentException('The namespace cannot be empty.');
            }

            if (!preg_match('/^[a-zA-Z0-9\\\\]+$/', $answer)) {
                throw new InvalidArgumentException('The namespace can only contain letters, numbers and backslashes.');
            }

            return $answer;
        });

        $namespaceDir = $this->choice('Directory of namespace autoloading', array_values(array_unique([
            'src',
            'src/app',
            'src/' . $this->package->vendor . '/' . $this->package->project,
            'src/' . unify_separator($namespace, '/'),
        ])), 0, 3, false);

        $namespaceDir = trim(substr($namespaceDir, 3), '/');

        $this->package->setInformation([
            'author_name'         => $authorName,
            'author_email'        => $authorEmail,
            'description'         => $description,
            'keywords'            => $keywords,
            'license'             => $license,
            'homepage'            => $homepage,
            'namespace'           => $namespace,
            'namespace_directory' => $namespaceDir,
        ]);

        return $this;
    }

    /**
     * Set resources for package.
     *
     * @return $this
     */
    protected function setResources()
    {
        $resources = [];

        if ($this->option('resources')) {
            $this->section('Choose Package Resources.');

            $choices = array_merge(['All'], array_map(function ($resource) {
                return Str::title(str_replace(['-', '_'], ' ', $resource));
            }, $this->builtinResources));

            $message   = 'Choose resources want to generate (separate by colon)';
            $resources = $this->choice($message, $choices, 0, 3, true, function ($answer) {
                $answer = array_map('trim', explode(',', $answer));

                if (in_array('All', $answer) || in_array(0, $answer) || in_array('0', $answer)) {
                    return '0';
                }

                return implode(',', $answer);
            });

            $resources = in_array('All', $resources) ? $this->builtinResources : array_map('Str::snake', $resources);
        }

        $this->package->setInformation('resources', $resources);

        return $this;
    }

    /**
     * Create package.
     *
     * @return bool
     */
    protected function createPackage()
    {
        $this->section('Perform Package Creation.');
        $this->info('This will take a bit of your time. Please wait.');
        $this->newLine();
        $this->write('Creating the package...');

        $result = $this->manager->create($this->package);

        if ($result) {
            $this->comment(' OK');
        } else {
            $this->newLine();
        }

        return $result;
    }

    /**
     * Install package.
     *
     * @return bool
     */
    protected function installPackage()
    {
        $this->section('Perform Package Installation.');
        $this->info('This will take a bit of your time. Please wait.');
        $this->newLine();
        $this->write('Installing the package...');

        $result = $this->manager->install($this->package->name);

        if ($result) {
            $this->comment(' OK');
        } else {
            $this->newLine();
        }

        return $result;
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
            ['author-name', null, InputOption::VALUE_OPTIONAL, 'Author name.'],
            ['author-email', null, InputOption::VALUE_OPTIONAL, 'Author email.'],
            ['description', null, InputOption::VALUE_OPTIONAL, 'Package description.'],
            ['keywords', null, InputOption::VALUE_OPTIONAL, 'Package keywords.'],
            ['license', null, InputOption::VALUE_OPTIONAL, 'License of package.'],
            ['homepage', null, InputOption::VALUE_OPTIONAL, 'Package homepage.'],
            ['resources', 'r', InputOption::VALUE_NONE, 'Request to create package with advanced resources.'],
            ['install', 'i', InputOption::VALUE_NONE, 'Request to install package after creation.'],
        ];
    }
}
