<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.public'] = __DIR__.'/fixtures';
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [\Eduardokum\LaravelMailAutoEmbed\ServiceProvider::class];
    }
}
