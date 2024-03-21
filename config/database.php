<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv-nav' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_SQLSRV_NAV'),
            'host' => env('DB_HOST_SQLSRV_NAV'),
            'port' => env('DB_PORT_SQLSRV_NAV'),
            'database' => env('DB_DATABASE_SQLSRV_NAV'),
            'username' => env('DB_USERNAME_SQLSRV_NAV'),
            'password' => env('DB_PASSWORD_SQLSRV_NAV'),
            'charset' => 'utf8',
            'prefix' => '',
            // 'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'sqlsrv-jobrouter' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_SQLSRV_ADDIN'),
            'host' => env('DB_HOST_SQLSRV_ADDIN'),
            'port' => env('DB_PORT_SQLSRV_ADDIN'),
            'database' => env('DB_DATABASE_SQLSRV_ADDIN'),
            'username' => env('DB_USERNAME_SQLSRV_ADDIN'),
            'password' => env('DB_PASSWORD_SQLSRV_ADDIN'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'sqlsrv-gnt' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_SQLSRV_GNT'),
            'host' => env('DB_HOST_SQLSRV_GNT', ''),
            'port' => env('DB_PORT_SQLSRV_GNT'),
            'database' => env('DB_DATABASE_SQLSRV_GNT'),
            'username' => env('DB_USERNAME_SQLSRV_GNT'),
            'password' => env('DB_PASSWORD_SQLSRV_GNT'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'sqlsrv-jobrouter-database' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_SQLSRV_JOBROUTER'),
            'host' => env('DB_HOST_SQLSRV_JOBROUTER'),
            'port' => env('DB_PORT_SQLSRV_JOBROUTER'),
            'database' => env('DB_DATABASE_SQLSRV_JOBROUTER'),
            'username' => env('DB_USERNAME_SQLSRV_JOBROUTER'),
            'password' => env('DB_PASSWORD_SQLSRV_JOBROUTER'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'sqlsrv-bc365-database' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_SQLSRV_BC365'),
            'host' => env('DB_HOST_SQLSRV_BC365'),
            'port' => env('DB_PORT_SQLSRV_BC365'),
            'database' => env('DB_DATABASE_SQLSRV_BC365'),
            'username' => env('DB_USERNAME_SQLSRV_BC365'),
            'password' => env('DB_PASSWORD_SQLSRV_BC365'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'sqlsrv-bcold-database' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_SQLSRV_BC365'),
            'host' => env('DB_HOST_SQLSRV_BC365'),
            'port' => env('DB_PORT_SQLSRV_BC365'),
            'database' => env('DB_DATABASE_SQLSRV_BCOLD'),
            'username' => env('DB_USERNAME_SQLSRV_BC365'),
            'password' => env('DB_PASSWORD_SQLSRV_BC365'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
