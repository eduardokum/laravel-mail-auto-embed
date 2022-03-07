<?php

namespace Eduardokum\LaravelMailAutoEmbed;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Eduardokum\LaravelMailAutoEmbed\Listeners\SymfonyEmbedImages;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
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
        $this->publishes([$this->getConfigPath() => config_path('mail-auto-embed.php')], 'config');


        Event::listen(function (MessageSending $event) {
            (new SymfonyEmbedImages($this->app['config']->get('mail-auto-embed')))
                ->handle($event);
        });

//        Mail::getSwiftMailer()->registerPlugin(new SwiftEmbedImages($this->app['config']->get('mail-auto-embed')));
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'mail-auto-embed');
    }

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        return __DIR__.'/../config/mail-auto-embed.php';
    }
}
