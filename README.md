[![Packagist](https://img.shields.io/packagist/v/eduardokum/laravel-mail-auto-embed.svg?style=flat-square)](https://github.com/eduardokum/laravel-mail-auto-embed)
[![Packagist](https://img.shields.io/packagist/dt/eduardokum/laravel-mail-auto-embed.svg?style=flat-square)](https://github.com/eduardokum/laravel-mail-auto-embed)
[![Packagist](https://img.shields.io/packagist/l/eduardokum/laravel-mail-auto-embed.svg?style=flat-square)](https://github.com/eduardokum/laravel-mail-auto-embed)
[![GitHub forks](https://img.shields.io/github/forks/eduardokum/laravel-mail-auto-embed.svg?style=social&label=Fork)](https://github.com/eduardokum/laravel-mail-auto-embed)

# Laravel Mail Auto Embed

## Install
You can install the package via composer:
```bash
$ composer require eduardokum/laravel-mail-auto-embed
```

You must install this service provider.

```php
$providers = [
    ...
    \Eduardokum\LaravelMailAutoEmbed\Providers\LaravelMailAutoEmbedServiceProvider::class,
    ...
 ];
```

## How it's works

It traverses the html rendered and replaces all the images that have a url by the same embedded in the body of the email.
To skip the replace, add the tag in the image `data-skip-embed`