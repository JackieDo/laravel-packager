<?php

namespace Jackiedo\Packager\Console\Commands;

use Jackiedo\Packager\Console\Command;

/**
 * The command to list all packages.
 *
 * @package jackiedo/laravel-packager
 *
 * @author Jackie Do <anhvudo@gmail.com>
 */
class ListPackageCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'packager:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all packages.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $tableHeader = ['Package Name', 'Package Path (from the base path)', 'Registered?', 'Installed?'];
        $allPackages = array_map(function ($info) {
            $info['registered'] = $info['registered'] ? 'yes' : '-';
            $info['installed']  = $info['installed'] ? 'yes' : '-';

            return $info;
        }, $this->manager->all());

        $countPackages = count($allPackages);

        if (0 == $countPackages) {
            $this->info('You don\'t have any packages.');

            return;
        }

        if (1 == $countPackages) {
            $this->info('You have one package as follow:');
        } else {
            $this->info('You have ' . $countPackages . ' packages as follow:');
        }

        $this->newLine();
        $this->table($tableHeader, $allPackages);
    }
}
