<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Driver
    |--------------------------------------------------------------------------
    |
    | The default driver used by CacheStore.
    |
    */

    'driver' => env(
        'CACHE_STORE_DRIVER',
        'file'
    ),



    /*
    |--------------------------------------------------------------------------
    | Cache Key Separator
    |--------------------------------------------------------------------------
    |
    | Separator used when building context-aware cache keys.
    |
    | Examples:
    |
    | tenant:school:key
    | tenant.school.key
    |
    */

    'key_separator' => env(
        'CACHE_STORE_KEY_SEPARATOR',
        ':'
    ),



    /*
    |--------------------------------------------------------------------------
    | Context Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix all generated keys.
    |
    | Example:
    |
    | schoolpalm:tenant:1:school:5:students
    |
    */

    'prefix' => env(
        'CACHE_STORE_PREFIX',
        'schoolpalm'
    ),



    /*
    |--------------------------------------------------------------------------
    | Context Settings
    |--------------------------------------------------------------------------
    */

    'context' => [

        /*
        | Include tenant identifier
        */
        'tenant' => true,


        /*
        | Include school identifier
        */
        'school' => true,

    ],



    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    */

    'drivers' => [

        'file' => [

            'path' => storage_path(
                'framework/cache'
            ),

        ],


        'redis' => [

            'connection' => env(
                'CACHE_STORE_REDIS_CONNECTION',
                'default'
            ),

        ],


        'database' => [

            'table' => 'cache',

        ],

    ],

];
