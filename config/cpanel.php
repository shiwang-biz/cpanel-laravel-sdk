<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WHM Connection
    |--------------------------------------------------------------------------
    |
    | Credentials for the WHM root/reseller account. Authentication uses
    | HTTP Basic Auth over the WHM JSON API, the same approach WHMCS uses
    | to manage cPanel accounts without storing per-account passwords.
    |
    */

    'host' => env('CPANEL_WHM_HOST'),

    'port' => env('CPANEL_WHM_PORT', 2087),

    'username' => env('CPANEL_WHM_USERNAME'),

    'password' => env('CPANEL_WHM_PASSWORD'),

    'verify_ssl' => env('CPANEL_WHM_VERIFY_SSL', true),

    'timeout' => env('CPANEL_WHM_TIMEOUT', 30),

];
