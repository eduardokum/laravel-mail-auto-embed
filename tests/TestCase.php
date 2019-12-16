<?php

namespace Rsvpify\LaravelMailAutoEmbed\Tests;

use Illuminate\Foundation\Application;
use Rsvpify\LaravelMailAutoEmbed\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param  Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.public'] = __DIR__ . '/fixtures';
    }

    /**
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
