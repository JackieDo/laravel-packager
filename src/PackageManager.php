<?php

namespace Jackiedo\Packager;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Jackiedo\Packager\Contracts\ManagerRepository;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * The package manager.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
class PackageManager implements ManagerRepository
{
    /**
     * The config repository.
     *
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The filesystem handler.
     *
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The package creator.
     *
     * @var Jackiedo\Packager\PackageCreator
     */
    protected $creator;

    /**
     * The path to directory used to store all packages.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * Create a new package creator instance.
     *
     * @param object $config  The config repository instance
     * @param object $files   The filesystem handler instance
     * @param object $creator The package creator
     *
     * @return void
     */
    public function __construct(Config $config, Filesystem $files, PackageCreator $creator)
    {
        $this->config      = $config;
        $this->files       = $files;
        $this->creator     = $creator;
        $this->storagePath = base_path('packages');
    }

    /**
     * Build path to package storage space.
     *
     * @param object|string $package The package instance or name of package
     *
     * @return string
     */
    public function packageStoragePath($package)
    {
        $packageDir = ($package instanceof Package) ? $package->name : $package;

        return unify_separator($this->storagePath . '/' . $packageDir);
    }

    /**
     * Determine if package exists.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function exists($packageName)
    {
        $packagePath  = $this->packageStoragePath($packageName);
        $composerKeys = $this->getJsonFileAsArray($packagePath . '/composer.json');

        if ($packageName == Arr::get($composerKeys, 'name')) {
            return true;
        }

        if ($this->getInstallStatus($packageName)['installed']) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the package has been registered to application repositories.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function isRegistered($packageName)
    {
        $composerKeys = $this->getJsonFileAsArray(base_path('composer.json'));
        $repositories = Arr::get($composerKeys, 'repositories', []);

        if (array_key_exists($packageName, $repositories)) {
            $repoType    = Arr::get($repositories, $packageName . '.type');
            $repoUrl     = Arr::get($repositories, $packageName . '.url');
            $expectedUrl = relative_path(base_path(), $this->packageStoragePath($packageName));

            return 'path' == $repoType && $expectedUrl == $repoUrl;
        }

        return false;
    }

    /**
     * Determine if the package has been installed.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function isInstalled($packageName)
    {
        if ($this->getInstallStatus($packageName)['installed']) {
            return true;
        }

        return false;
    }

    /**
     * Get install status of the package.
     *
     * @param string $packageName The package name
     *
     * @return array
     */
    public function getInstallStatus($packageName)
    {
        $lockKeys     = $this->getJsonFileAsArray(base_path('composer.lock'));
        $allInstalled = array_merge(Arr::get($lockKeys, 'packages', []), Arr::get($lockKeys, 'packages-dev', []));
        $status       = [
            'installed'   => false,
            'required_by' => [],
        ];

        foreach ($allInstalled as $installed) {
            $installedName = Arr::get($installed, 'name');

            if ($installedName == $packageName) {
                $status['installed'] = $status['installed'] || true;
            }

            $require    = Arr::get($installed, 'require', []);
            $requireDev = Arr::get($installed, 'require-dev', []);

            if (array_key_exists($packageName, $require) || array_key_exists($packageName, $requireDev)) {
                $status['required_by'][] = $installedName;
            }
        }

        $status['required_by'] = array_unique($status['required_by']);

        return $status;
    }

    /**
     * Get all packages.
     *
     * @return array
     */
    public function all()
    {
        if (!$this->files->isDirectory($this->storagePath)) {
            return [];
        }

        $packages        = [];
        $composersFinder = new Finder;

        $composersFinder->files()->name('composer.json')->in($this->storagePath)->depth('==2');

        foreach ($composersFinder as $composerFile) {
            $packageComposer = $this->getJsonFileAsArray($composerFile);
            $packageName     = Arr::get($packageComposer, 'name', 'unknown/unkown');

            $packages[] = [
                'name'       => $packageName,
                'path'       => relative_path(base_path(), $composerFile->getPath()),
                'registered' => $this->isRegistered($packageName),
                'installed'  => $this->isInstalled($packageName),
            ];
        }

        return $packages;
    }

    /**
     * Create a package.
     *
     * @param object $package The package instance
     *
     * @return bool
     */
    public function create(Package $package)
    {
        $result = $this->creator->create($package->lock(), $this->packageStoragePath($package));

        if ($result) {
            $this->register($package->name);
        }

        return $result;
    }

    /**
     * Remove a package.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function remove($packageName)
    {
        $this->unregister($packageName);

        $packagePath = $this->packageStoragePath($packageName);
        $vendorPath  = dirname($packagePath);

        if (is_dir($packagePath)) {
            $result = $this->files->deleteDirectory($packagePath);

            if (empty_dir($vendorPath)) {
                $this->files->deleteDirectory($vendorPath);
            }

            return $result;
        }

        return true;
    }

    /**
     * Register the package to application repositories.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function register($packageName)
    {
        $configMinimumStability = $this->runProcess([
            'composer',
            'config',
            'minimum-stability',
            'dev',
        ]);

        $configPreferStable = $this->runProcess([
            'composer',
            'config',
            'prefer-stable',
            'true',
        ]);

        $configRepositories = $this->runProcess([
            'composer',
            'config',
            'repositories.' . $packageName,
            'path',
            relative_path(base_path(), $this->packageStoragePath($packageName)),
        ]);

        return $configMinimumStability && $configPreferStable && $configRepositories;
    }

    /**
     * Unregister the package from application repositories.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function unregister($packageName)
    {
        $command = [
            'composer',
            'config',
            '--unset',
            'repositories.' . $packageName,
        ];

        return $this->runProcess($command);
    }

    /**
     * Install the package.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function install($packageName)
    {
        $command = [
            'composer',
            'require',
            $packageName,
        ];

        return $this->runProcess($command, null, null, null);
    }

    /**
     * Uninstall the package.
     *
     * @param string $packageName The package name
     *
     * @return bool
     */
    public function uninstall($packageName)
    {
        $command = [
            'composer',
            'remove',
            $packageName,
        ];

        return $this->runProcess($command, null, null, null);
    }

    /**
     * Get content of json file as array.
     *
     * @param string $file The path to json file
     *
     * @return array
     */
    protected function getJsonFileAsArray($file)
    {
        if ($this->files->isFile($file)) {
            return json_decode($this->files->get($file), true);
        }

        return [];
    }

    /**
     * Run a process.
     *
     * @param array  $command     The command to run and its arguments listed as separate entries
     * @param string $workingDir  The working directory of the process
     * @param float  $timeout     Sets the process timeout (max. runtime) in seconds.
     *                            Set this value to null to disable timeout.
     * @param float  $idleTimeout Sets the process idle timeout (max. time since last output) in seconds.
     *                            Set this value to null to disable timeout.
     *
     * @return bool
     */
    protected function runProcess(array $command, $workingDir = null, $timeout = 60, $idleTimeout = 60)
    {
        $workingDir     = $workingDir ?: base_path();
        $laravelVersion = app()->version();

        if (version_compare($laravelVersion, '5.6.0', '>=')) {
            $process = new Process($command, $workingDir);
        } else {
            $process = new Process(implode(' ', $command), $workingDir);
        }

        $process->setTimeout($timeout);
        $process->setIdleTimeout($idleTimeout);
        $process->run();

        return 0 === $process->getExitCode();
    }
}
