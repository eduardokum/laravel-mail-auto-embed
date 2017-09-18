<?php
namespace Eduardokum\LaravelMailAutoEmbed\Providers;

use Illuminate\Support\ServiceProvider;
use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Mail;

class LaravelMailAutoEmbedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
         Mail::getSwiftMailer()->registerPlugin(new SwiftEmbedImages());
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
