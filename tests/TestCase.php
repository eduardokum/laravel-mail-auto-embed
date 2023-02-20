<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests;

use Illuminate\Foundation\Application;

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
        $path = __DIR__ . '/fixtures';

        if(version_compare(Application::VERSION, '10', '<')){
            $app['path.public'] = $path;
        }else{
            $app->usePublicPath($path);
        }
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
     * Returns a library file.
     * @param string $name
     * @return string
     */
    protected function getLibraryFile($name)
    {
        $path = __DIR__.'/lib/'.$name;
        if (! \file_exists($path) || ! \is_file($path)) {
            $this->fail("Cannot find {$name} in file library");
        }

        return \file_get_contents($path);
    }
}
