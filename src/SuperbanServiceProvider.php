<?php

namespace Delaney\Superban;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class SuperbanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootConfig();
        $this->bootMiddleware();
    }

    /**
     * Boot the package's configuration file.
     *
     * @return void
     */
    protected function bootConfig()
    {
        $this->publishes([
            __DIR__.'/../config/courier.php' => config_path('superban.php'),
        ], 'superban');
    }

    /**
     * Boot the package's middleware.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function bootMiddleware()
    {
        $this->app->make('route')->aliasMiddleware(
            'superban',
            \Delaney\Superban\Middleware\Superban::class
        );
    }
}
