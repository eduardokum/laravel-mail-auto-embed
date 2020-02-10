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

    /**
     * Returns a library file
     * @param string $name
     * @return string
     */
    protected function getLibraryFile($name)
    {
        $path = __DIR__ . '/lib/' . $name;
        if (!\file_exists($path) || !\is_file($path)) {
            $this->fail("Cannot find {$name} in file library");
        }

        return \file_get_contents($path);
    }
}
