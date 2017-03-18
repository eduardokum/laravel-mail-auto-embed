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

## Usage

Its use is very simple, you write your markdown normally:

```
@component('mail::message')
# Order Shipped

Your order has been shipped!

@component('mail::button', ['url' => $url])
View Order
@endcomponent

Purchased product:

![product](https://domain.com/products/product-1.png)

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

When sending, it will replace the link that would normally generated `<img src="https://domain.com/products/product-1.png">` by the embed of the image eg `<img src = "cid:3991f143cf1a86257f8671883736613c@Swift.generated">` and attach the file in the email.

Also works for raw html too:

```
// eg: resources/vendor/mail/html/header.blade.php
<tr>
    <td class="header">
        <a href="{{ $url }}">
            <img src="https://domain.com/logo.png" class="img-header">
        </a>
    </td>
</tr>
```

If you do not want to embed the image to use some kind of image tracker, simply add the attribute `data-skip-embed` in image tag.

```html
<img src="https://domain.com/logo.png" data-skip-embed class="img-header">
```


## How it's works

It traverses the html rendered and replaces all the images that have a url by the same embedded in the body of the email.
To skip the replace, add the tag in the image `data-skip-embed`
