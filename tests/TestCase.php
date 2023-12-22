<?php

namespace Delaney\Superban\Tests;

use Delaney\Superban\SuperbanServiceProvider;
use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Setup middleware
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SuperbanServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('superban.drivers', [
                'file',
            ]);
        });
    }
}