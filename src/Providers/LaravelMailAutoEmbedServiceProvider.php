<?php

namespace Eduardokum\LaravelMailAutoEmbed\Providers;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

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
