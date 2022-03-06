<?php

namespace Jackiedo\Packager;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jackiedo\Packager\Contracts\CreatorRepository;

/**
 * The package creator.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
class PackageCreator implements CreatorRepository
{
    /**
     * The config repository.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The filesystem handler.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path to directory used to store temporary files of formatting stub.
     *
     * @var string
     */
    protected $tempStorage;

    /**
     * Default directories of resource.
     *
     * @var array
     */
    protected $defaultResourceDirs = [
        'namespace' => [
            'facade'     => 'Facades',
            'interface'  => 'Contracts',
            'abstract'   => 'Contracts',
            'trait'      => 'Traits',
            'exception'  => 'Exceptions',
            'controller' => 'Http/Controllers',
            'middleware' => 'Http/Middleware',
            'model'      => 'Models',
            'command'    => 'Console/Commands',
        ],
        'none_namespace' => [
            'config'    => 'config',
            'migration' => 'database/migrations',
            'assets'    => 'resources/assets',
            'lang'      => 'resources/lang',
            'view'      => 'resources/views',
            'route'     => 'routes',
            'helper'    => 'helpers',
        ],
    ];

    /**
     * The package instance.
     *
     * @var \Jackiedo\Workbench\Package
     */
    protected $package;

    /**
     * The path to directory used to store all files of package.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * The paths to directory of resource.
     *
     * @var array
     */
    protected $resourceDirPaths = [];

    /**
     * Stubs versions compatible with Laravel thread version.
     *
     * @var array
     */
    protected $compatibleStubSet = [];

    /**
     * Create a new package creator instance.
     *
     * @param Config     $config The config repository instance
     * @param Filesystem $files  The filesystem handler instance
     *
     * @return void
     */
    public function __construct(Config $config, Filesystem $files)
    {
        $this->config      = $config;
        $this->files       = $files;
        $this->tempStorage = $this->config->get('packager.temporary_storage') ?: storage_path('packager/temp');
    }

    /**
     * Dynamic property accesstor.
     *
     * @param string $property The property name
     *
     * @return mixed
     */
    public function __get($property)
    {
        $getterMethod = Str::camel('get_' . $property);

        if (method_exists($this, $getterMethod)) {
            return call_user_func_array([$this, $getterMethod], []);
        }

        return $this->{$property};
    }

    /**
     * Create package with a given storage path.
     *
     * @param Package     $package              The package instance
     * @param string      $storeAt              The path to directory used to store the package
     * @param null|string $lowestLaravelVersion Lowest Laravel thread version that the package supports
     *
     * @return bool
     */
    public function create(Package $package, $storeAt, $lowestLaravelVersion = null)
    {
        $this->setCompatibleStubSet($lowestLaravelVersion);

        $this->package          = $package;
        $this->storagePath      = $storeAt;
        $this->resourceDirPaths = $this->buildResourceDirPaths($package->resources);

        // Create temporary storage
        $this->makeDirectory($this->tempStorage);
        $this->files->put($this->tempStorage . '/.gitignore', '*' . PHP_EOL . '!.gitignore');

        // Create the necessary directories
        $this->createStorageDir();
        $this->createSrcDir();
        $this->createNamespaceDir();
        $this->createTestsDir();

        // Create basic files
        $this->createBasicFiles();

        // If package has resources, create resource files
        if (!$this->isBasicCreation()) {
            $this->createResourceFiles();
        }

        // Clean temporary storage if needed
        if ($this->config->get('packager.delete_temp_after_do', false)) {
            $this->files->cleanDirectory($this->tempStorage);
        }

        return true;
    }

    /**
     * Get path to the src directory.
     *
     * @return string
     */
    public function getSrcDirPath()
    {
        return normalize_path($this->storagePath . '/src');
    }

    /**
     * Get path to the tests directory.
     *
     * @return string
     */
    public function getTestsDirPath()
    {
        return normalize_path($this->storagePath . '/tests');
    }

    /**
     * Get path to the namespace directory of package.
     *
     * @return string
     */
    public function getNamespaceDirPath()
    {
        $namespaceDir = trim($this->package->namespace_directory);

        if (!empty($namespaceDir)) {
            return normalize_path($this->getSrcDirPath() . '/' . $namespaceDir);
        }

        return $this->getSrcDirPath();
    }

    /**
     * Get path to directory of resource.
     *
     * @param string $resource Resource name
     *
     * @return string
     */
    public function getResourceDirPath($resource)
    {
        return normalize_path(Arr::get($this->resourceDirPaths, $resource));
    }

    /**
     * Get path to resource file.
     *
     * @param string $resource
     *
     * @return string
     */
    public function getResourcePath($resource)
    {
        $path = $this->resourceDirPaths[$resource];

        if ('lang' == Str::lower($resource)) {
            return normalize_path($path . '/en//' . $this->getResourceBaseName('lang'));
        }

        return normalize_path($path . '/' . $this->getResourceBaseName($resource));
    }

    /**
     * Get resource filename.
     *
     * @param string $resource
     *
     * @return string
     */
    public function getResourceBaseName($resource)
    {
        switch (Str::lower($resource)) {
            case 'config':
                return 'config.php';
                break;

            case 'migration':
                return date('Y_m_d_His') . '_create_' . $this->package->snake_project . '_table.php';
                break;

            case 'lang':
                return 'demo.php';
                break;

            case 'view':
                return 'demo.blade.php';
                break;

            case 'facade':
                return $this->package->project . '.php';
                break;

            case 'interface':
                return $this->package->project . 'Interface.php';
                break;

            case 'abstract':
                return $this->package->project . 'Abstract.php';
                break;

            case 'controller':
                return $this->package->project . 'Controller.php';
                break;

            case 'model':
                return $this->package->project . 'Model.php';
                break;

            case 'middleware':
                return $this->package->project . 'Middleware.php';
                break;

            case 'route':
                return 'routes.php';
                break;

            case 'command':
                return 'DemoCommand.php';
                break;

            case 'trait':
                return 'DemoTrait.php';
                break;

            case 'exception':
                return $this->package->project . 'Exception.php';
                break;

            case 'helper':
                return 'helpers.php';
                break;

            default:
                return '.gitkeep';
                break;
        }
    }

    /**
     * Get resource name part only from filename.
     *
     * @param string $resource
     *
     * @return string
     */
    public function getResourceName($resource)
    {
        return $this->files->name($this->getResourceBaseName($resource));
    }

    /**
     * Get namespace of the resource.
     *
     * @param string $resource The resource name
     *
     * @return string
     */
    public function getResourceNamespace($resource)
    {
        $namespace = $this->package->namespace . '\\' . relative_path($this->getNamespaceDirPath(), $this->getResourceDirPath($resource), '\\');

        return rtrim($namespace, '\\');
    }

    /**
     * Determine if the creation is basic type (no build resources).
     *
     * @return bool
     */
    public function isBasicCreation()
    {
        return 0 == count($this->resourceDirPaths);
    }

    /**
     * Setup compatible stub versions.
     *
     * @param null|string $lowestLaravelVersion Lowest Laravel thread version that stub file compatible with
     *
     * @return void
     */
    protected function setCompatibleStubSet($lowestLaravelVersion = null)
    {
        $lowestLaravelVersion    = $lowestLaravelVersion ?: $this->laravelThreadVersion();
        $this->compatibleStubSet = $this->matchStubSet($lowestLaravelVersion);
    }

    /**
     * Return thread version from Laravel version of application.
     *
     * @return string
     */
    protected function laravelThreadVersion()
    {
        $versionParts = explode('.', app()->version());

        array_pop($versionParts);

        if ((int) $versionParts[0] >= 6) {
            return $versionParts[0] . '.0';
        }

        return implode('.', $versionParts);
    }

    /**
     * Create directory used to store package's files.
     *
     * @return $this
     */
    protected function createStorageDir()
    {
        $this->makeDirectory($this->storagePath);

        return $this;
    }

    /**
     * Create the src directory.
     *
     * @return $this
     */
    protected function createSrcDir()
    {
        $this->makeDirectory($this->getSrcDirPath());

        return $this;
    }

    /**
     * Create the tests directory.
     *
     * @return $this
     */
    protected function createTestsDir()
    {
        $path = $this->getTestsDirPath();

        $this->makeDirectory($path);
        $this->copyStub('gitkeep', $path . '/.gitkeep', false);

        return $this;
    }

    /**
     * Create the namspace directory of package.
     *
     * @return $this
     */
    protected function createNamespaceDir()
    {
        $this->makeDirectory($this->getNamespaceDirPath());

        return $this;
    }

    /**
     * Create basic files of package.
     *
     * @return $this
     */
    protected function createBasicFiles()
    {
        // Create .gitignore
        $this->copyStub('gitignore', $this->storagePath . '/.gitignore', false);

        // Create phpunit.xml
        $this->copyStub('phpunit', $this->storagePath . '/phpunit.xml', false);

        // Create .travis.yml
        $this->copyStub('travis', $this->storagePath . '/.travis.yml', false);

        // Create composer.json
        $this->copyStub('composer', $this->storagePath . '/composer.json');

        // Create main class
        $this->copyStub('main_class', $this->namespaceDirPath . '/' . $this->package->project . '.php');

        // Create service provider
        $this->copyStub('service_provider', $this->namespaceDirPath . '/' . $this->package->project . 'ServiceProvider.php');

        return $this;
    }

    /**
     * Create resource files of package.
     *
     * @return $this
     */
    protected function createResourceFiles()
    {
        foreach ($this->resourceDirPaths as $resource => $path) {
            $resourcePath    = $this->getResourcePath($resource);
            $resourceDirPath = dirname($resourcePath);

            $this->makeDirectory($resourceDirPath);

            if ('.gitkeep' == pathinfo($resourcePath, PATHINFO_BASENAME)) {
                $this->copyStub('gitkeep', $resourcePath);
            } else {
                $this->copyStub($resource, $resourcePath);
            }
        }

        return $this;
    }

    /**
     * Build paths to resource files of package.
     *
     * @param array $resources The resources of package
     *
     * @return array
     */
    protected function buildResourceDirPaths(array $resources = [])
    {
        $paths = [];

        if (0 < count($resources)) {
            $defaultDirs = $this->defaultResourceDirs;
            $configDirs  = $this->config->get('packager.skeleton_structure', []);

            foreach ($resources as $resource) {
                // If resource using namespace
                if (array_key_exists($resource, $defaultDirs['namespace'])) {
                    $directory = Arr::get($configDirs, $resource, $defaultDirs['namespace'][$resource]);
                    $directory = $this->standardizeDirComponent($directory, true);

                    $paths[$resource] = $this->getNamespaceDirPath() . '/' . $directory;

                // Resource do not use namespace
                } else {
                    $directory = Arr::get($configDirs, $resource, $defaultDirs['none_namespace'][$resource]);
                    $directory = $this->standardizeDirComponent($directory);

                    $paths[$resource] = $this->getSrcDirPath() . '/' . $directory;
                }
            }
        }

        return $paths;
    }

    /**
     * Standardize the name of components in the directory.
     *
     * @param string $directory
     * @param bool   $titleCase
     *
     * @return string
     */
    protected function standardizeDirComponent($directory, $titleCase = false)
    {
        $directory = trim(normalize_path($directory, '/'), '/');

        // Standardize the name of components in the directory
        $segments = array_map(function ($component) use ($titleCase) {
            return $titleCase ? Str::title(Str::snake($component)) : str_replace(' ', '_', $component);
        }, explode('/', $directory));

        return implode('/', $segments);
    }

    /**
     * Make a directory.
     *
     * @param string $directory
     *
     * @return $this
     */
    protected function makeDirectory($directory)
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0777, true);
        }

        return $this;
    }

    /**
     * Copy content of stub file to specific file.
     *
     * @param string $stubName       The stub file name without extension
     * @param string $toFile         The path to new file with extension
     * @param bool   $formatContent  Indicates need format stub content before put to new file
     * @param mixed  $defaultContent Default content if stub file not exists
     *
     * @return $this
     */
    protected function copyStub($stubName, $toFile, $formatContent = true, $defaultContent = null)
    {
        $stubFile = $this->getStubFilePath($stubName);
        $content  = !is_null($stubFile) ? $this->files->get($stubFile) : $defaultContent;

        if ($formatContent) {
            $content = $this->formatContent($content);
        }

        $this->files->put($toFile, $content);

        return $this;
    }

    /**
     * Format stub content.
     *
     * @param string $content The stub content
     *
     * @return string
     */
    protected function formatContent($content)
    {
        $content = (string) $content;

        // Handle the @import directive
        $content = preg_replace_callback('/\h*\{\{\@import\h+(.*)\h+\@import\}\}\h*\r*\n+/U', function ($match) {
            $matchContent = $match[1];

            if (Str::contains($matchContent, '|')) {
                // Only import file if the package has specific resource
                $segments = explode('|', $matchContent);

                if (!in_array(trim($segments[1]), $this->package->resources)) {
                    return null;
                }

                $importFile = $this->getStubFilePath('pieces/' . $segments[0]);
            } else {
                // Import file without condition
                $importFile = $this->getStubFilePath('pieces/' . $matchContent);
            }

            $importContent = !is_null($importFile) ? $this->files->get($importFile) : null;

            return $this->formatContent($importContent);
        }, $content);

        // Handle the @package directive
        $content = preg_replace_callback('/\{\{\@package\h+(.*)\h+\@package\}\}/U', function ($match) {
            $packageInfo = $match[1];

            return $this->package->{$packageInfo};
        }, $content);

        // handle the @callback directive handle
        return preg_replace_callback('/\{\{\@callback\s*(.*)\s*\@callback\}\}/msU', function ($match) {
            $code = $match[1];

            // Evaluate code
            $hash       = Str::random(40);
            $tmpFile    = $this->tempStorage . '/' . $hash;
            $tmpContent = "\t" . 'function _' . $hash . '($creator) {' . PHP_EOL . $code . PHP_EOL . "\t" . '}';
            $tmpContent = 'if (!function_exists("_' . $hash . '")) {' . PHP_EOL . $tmpContent . PHP_EOL . '}';
            $tmpContent = $tmpContent . PHP_EOL . PHP_EOL . 'return _' . $hash . '($this);';

            $this->files->put($tmpFile, '<?php' . PHP_EOL . PHP_EOL . $tmpContent . PHP_EOL);

            $executed = require $tmpFile;

            return $executed;
        }, $content);
    }

    /**
     * Get the path to stub file compatible with the Laravel thread version.
     *
     * @param string $stub The path to stub file from stub folder (without extension)
     *
     * @return null|string
     */
    protected function getStubFilePath($stub)
    {
        $stub = trim((string) $stub, '/\\');

        foreach ($this->compatibleStubSet as $folder) {
            $stubFile = normalize_path(__DIR__ . '/stubs//' . $folder . '/' . $stub . '.stub');

            if ($this->files->isFile($stubFile)) {
                return $stubFile;
            }
        }

        return null;
    }

    /**
     * Find stub versions compatible with specific Laravel thread version.
     *
     * @param string $limitVersion Laravel thread version use to as limit
     *
     * @return array
     */
    protected function matchStubSet($limitVersion)
    {
        $stubSubFolders = array_map(function ($path) {
            return basename($path);
        }, $this->files->glob(__DIR__ . '/stubs/*', GLOB_ONLYDIR));

        $versionFolders = array_values(array_filter($stubSubFolders, function ($name, $index) use ($limitVersion) {
            $version = $this->getFolderVersion($name);

            return preg_match('/^\d+\.\d+$/', $version) && version_compare($version, $limitVersion, '<=');
        }, ARRAY_FILTER_USE_BOTH));

        uksort($versionFolders, function ($front, $behind) {
            $front  = $this->getFolderVersion($front);
            $behind = $this->getFolderVersion($behind);

            return version_compare($behind, $front);
        });

        array_push($versionFolders, 'default');

        return $versionFolders;
    }

    /**
     * Get version string from folder name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getFolderVersion($name)
    {
        return str_replace('laravel_', '', $name);
    }
}
