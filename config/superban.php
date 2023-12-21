<?php

/*
 * Config file for the Superban package.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Superban - Cache Stores
    |--------------------------------------------------------------------------
    |
    | This is an array of cache stores to use with Superban.
    | Leaving this as empty would use the default cache store.
    |
    | Example:
    | ['redis', 'file']
    |
    */
    'drivers' => [],

    /*
    |--------------------------------------------------------------------------
    | Superban - Ban IP Addresses
    |--------------------------------------------------------------------------
    |
    | Add the IP address when banning a client.
    |
    */
    'ban_ip_addresses' => env('SUPERBAN_BAN_IP', true),

    /*
    |--------------------------------------------------------------------------
    | Superban - User Keys
    |--------------------------------------------------------------------------
    |
    | This is a list of keys to ban the client user.
    | These keys should be available on your user model.
    |
    */
    'user_keys' => ['id', 'email'],

    /*
    |--------------------------------------------------------------------------
    | Superban - Message
    |--------------------------------------------------------------------------
    |
    | Message to display to a banned client in the HTTP Response
    |
    | Default: "You have been banned temporarily."
    |
    */
    'message' => env('SUPERBAN_ERROR_MESSAGE'),
];
