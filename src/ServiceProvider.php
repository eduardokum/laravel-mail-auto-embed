<?php

namespace Eduardokum\LaravelMailAutoEmbed;

use Eduardokum\LaravelMailAutoEmbed\Contracts\Listeners\EmbedImages;
use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Eduardokum\LaravelMailAutoEmbed\Listeners\SymfonyEmbedImages;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Throwable;

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

        $this->app->singleton(EmbedImages::class, function($app) {
            if (version_compare(Application::VERSION, '9.0.0', '>=')) {
                return new SymfonyEmbedImages($app['config']->get('mail-auto-embed'));
            }
            return new SwiftEmbedImages($app['config']->get('mail-auto-embed'));
        });

        if (version_compare(Application::VERSION, '9.0.0', '>=')) {
            Event::listen(function (MessageSending $event) {
                $this->app->make(EmbedImages::class)->beforeSendPerformed($event);
            });
        } else {
            foreach (Arr::get($this->app['config'], 'mail.mailers', []) as $driver => $mailer) {
                try {
                    // If transport not exists this will throw an exception
                    Mail::driver($driver)->getSwiftMailer()->registerPlugin($this->app->make(EmbedImages::class));
                } catch (Throwable $e) {}
            }
        }
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
