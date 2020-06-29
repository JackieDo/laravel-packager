<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default package author name
    |--------------------------------------------------------------------------
    |
    | This setting allows suggestions for the author's name whenever using the
    | Artisan "packager:new" command.
    |
    */

    'default_author_name' => null,

    /*
    |--------------------------------------------------------------------------
    | Default package author e-mail address
    |--------------------------------------------------------------------------
    |
    | This setting allows suggestions for the author's email whenever using the
    | Artisan "packager:new" command.
    |
    */

    'default_author_email' => null,

    /*
    |--------------------------------------------------------------------------
    | Default package license
    |--------------------------------------------------------------------------
    |
    | This setting allows suggestions for the package license whenever using
    | the Artisan "packager:new" command.
    |
    */

    'default_license' => null,

    /*
    |--------------------------------------------------------------------------
    | Skeleton resources structure
    |--------------------------------------------------------------------------
    |
    | This setting allows to set the path to the directories where the
    | skeleton's resource files are stored.
    |
    | There are two type of skeleton resources. First type is resources that
    | using namespace (such as facade, abstract, interface, controller...). The
    | second is resources do not use namespace (such as config, view...).
    |
    | The directories of resources that using namespace will be placed in the
    | directory of namespace autoloading (will be asked whenever you run the
    | command). The directories of remaining resources will be placed in the
    | "src" directory.
    |
    */

    'skeleton_structure' => [
        // Resources using namespace
        'facade'     => 'Facades',
        'interface'  => 'Contracts',
        'abstract'   => 'Contracts',
        'trait'      => 'Traits',
        'exception'  => 'Exceptions',
        'controller' => 'Http/Controllers',
        'middleware' => 'Http/Middleware',
        'model'      => 'Models',
        'command'    => 'Console/Commands',

        // Resource don't use namespace
        'config'    => 'config',
        'migration' => 'database/migrations',
        'assets'    => 'resources/assets',
        'lang'      => 'resources/lang',
        'view'      => 'resources/views',
        'route'     => 'routes',
        'helper'    => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary storage path of creation
    |--------------------------------------------------------------------------
    |
    | This setting allow to set the path to temporary directory of creation.
    |
    */

    'temporary_storage' => storage_path('packager/temp'),

    /*
    |--------------------------------------------------------------------------
    | Delete temporary files after finish
    |--------------------------------------------------------------------------
    |
    | This setting indicates whether or not to delete temporary files created
    | during package creation.
    |
    */

    'delete_temp_after_do' => true,

];
