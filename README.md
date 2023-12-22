# Superban

Ban clients from your API for a period of time when they make too many requests within a set time.

## Installation

In your Laravel app, using Composer, install the package and publish the configuration file:

```
composer require delaney\superban
```

## Configuration & Usage

Publish the configuration file with this command:
```
php artisan vendor:publish --tag=superban
```

`config\superban`

```
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

```

You can specify options in your `.env` file using:

```
SUPERBAN_BAN_IP=false
SUPERBAN_ERROR_MESSAGE="This request is banned"
```

### Using the middleware
To add Superban to your routes, simply use it like you would any other middleware, using the alias `superban` and specifying your parameters, with time in minutes. For example:

```
Route::post('clear')->middleware('superban:100,60,1440');

Route::prefix('/resources')->middleware('superban:20,1,10')
    ->group(function() {
        Route::get() ...
```

In the first line, users who make 100 requests within 1 hour (60 minutes) will get banned for 24 hours (1440). And in the second middleware group, users exceeding 20 requests in a minute will get banned for 10 minutes.

## License

MIT