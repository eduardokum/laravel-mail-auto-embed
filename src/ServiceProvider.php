<?php

namespace Eduardokum\LaravelMailAutoEmbed;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
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
