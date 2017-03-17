<?php
namespace LaravelMailAutoEmbed\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Mail;

class MailAutoEmbedServiceProvider extends ServiceProvider
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
