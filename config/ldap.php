<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LDAP Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the LDAP connections below you wish
    | to use as your default connection for all LDAP operations. Of
    | course you may add as many connections you'd like below.
    |
    */

    'default' => env('LDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Connections
    |--------------------------------------------------------------------------
    |
    | Below you may configure each LDAP connection your application requires
    | access to. Be sure to include a valid base DN - otherwise you may
    | not receive any results when performing LDAP search operations.
    |
    */

    'connections' => [

        'default' => [
            'hosts' => [env('LDAP_HOST', 'assdc2.domass.local')],
            'username' => env('LDAP_USERNAME', 'neoticket@domass'),
            'password' => env('LDAP_PASSWORD', 'nt123456!'),
            'port' => env('LDAP_PORT', 636),
            'base_dn' => env('LDAP_BASE_DN', 'dc=domass,dc=local'),
            'timeout' => env('LDAP_TIMEOUT', 5),
            'use_ssl' => env('LDAP_SSL', true),
            'use_tls' => env('LDAP_TLS', false),
            'use_sasl' => env('LDAP_SASL', false),
            'sasl_options' => [
                // 'mech' => 'GSSAPI',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Logging
    |--------------------------------------------------------------------------
    |
    | When LDAP logging is enabled, all LDAP search and authentication
    | operations are logged using the default application logging
    | driver. This can assist in debugging issues and more.
    |
    */

    'logging' => [
        'enabled' => env('LDAP_LOGGING', false),
        'channel' => env('LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Cache
    |--------------------------------------------------------------------------
    |
    | LDAP caching enables the ability of caching search results using the
    | query builder. This is great for running expensive operations that
    | may take many seconds to complete, such as a pagination request.
    |
    */

    'cache' => [
        'enabled' => env('LDAP_CACHE', true),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

];
