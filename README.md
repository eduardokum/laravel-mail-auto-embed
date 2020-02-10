[![Packagist](https://img.shields.io/packagist/v/eduardokum/laravel-mail-auto-embed.svg?style=flat-square)](https://github.com/eduardokum/laravel-mail-auto-embed)
[![Packagist](https://img.shields.io/packagist/dt/eduardokum/laravel-mail-auto-embed.svg?style=flat-square)](https://github.com/eduardokum/laravel-mail-auto-embed)
[![Packagist](https://img.shields.io/packagist/l/eduardokum/laravel-mail-auto-embed.svg?style=flat-square)](https://github.com/eduardokum/laravel-mail-auto-embed)
[![GitHub forks](https://img.shields.io/github/forks/eduardokum/laravel-mail-auto-embed.svg?style=social&label=Fork)](https://github.com/eduardokum/laravel-mail-auto-embed)

# Laravel Mail Auto Embed

Automatically parses your messages and embeds the images found into your mail,
replacing the original online-version of the image.

Should work on Laravel 5.3+. Automatically tested for Laravel 5.4+ on PHP 7.0+.

## Install

You can install the package via composer:
```shell
composer require eduardokum/laravel-mail-auto-embed
```

This package uses Laravel 5.5 Package Auto-Discovery.
For previous versions of Laravel, you need to add the following Service
Provider:

```php
$providers = [
    ...
    \Eduardokum\LaravelMailAutoEmbed\ServiceProvider::class,
    ...
 ];
```


## Usage

Its use is very simple, you write your markdown normally:

```markdown
<!-- eg: resources/vendor/mail/markdown/order-shipped.blade.php -->
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

When sending, it will replace the link that would normally be generated:
> `<img src="https://domain.com/products/product-1.png">`

by an embedded inline attachment of the image:
> `<img src="cid:3991f143cf1a86257f8671883736613c@Swift.generated">`.

It works for raw html too:

```html
<!-- eg: resources/vendor/mail/html/header.blade.php -->
<tr>
    <td class="header">
        <a href="{{ $url }}">
            <img src="https://domain.com/logo.png" class="img-header">
        </a>
    </td>
</tr>
```

If you do not want to use automatic embedding for specific images (because they
are hosted elsewhere, if you want to use some kind of image tracker, etc.),
simply add the attribute `data-skip-embed` in the image tag:

```html
<img src="https://domain.com/logo.png" data-skip-embed class="img-header">
```
### Local resources

For local resources that are not available publicly, use `file://` urls:

```html
<img src="file://{{ resource_path('assets/img/logo.png') }}" alt="Logo" border="0"/>
```

## Configuration

The defaults are set in `config/mail-auto-embed.php`. You can copy this file to
your own config directory to modify the values using this command:

```shell
php artisan vendor:publish --provider="Eduardokum\LaravelMailAutoEmbed\ServiceProvider"
```

### Explicit embedding configuration

By default, images are embedded automatically, unless you add the
`data-skip-embed` attribute.

You can also disable auto-embedding globally by setting the `MAIL_AUTO_EMBED`
environment variable to `false`, or by modifying the `enabled` property in the
published config. You can then enable embedding for individual images with the
`data-auto-embed` attribute.

```env
# .env
MAIL_AUTO_EMBED=false
```

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Mail auto embed
    |--------------------------------------------------------------------------
    |
    | If true, images will be automatically embedded.
    | If false, only images with the 'data-auto-embed' attribute will be embedded
    |
    */
    'enabled' => false,

    // …
];
```

```html
<p>
    <!-- Won't be embedded -->
    <img src="https://domain.com/logo.png" class="img-header">
</p>
<p>
    <!-- Explicit embedding -->
    <img src="https://domain.com/item.png"  data-auto-embed>
</p>
```

### Base64 embedding

If you prefer to use Base64 instead of inline attachments, you can do so by
setting the `MAIL_AUTO_EMBED_METHOD` environment variable or the `method`
config property to `base64`.

```php
return [
    // …

    /*
    |--------------------------------------------------------------------------
    | Mail embed method
    |--------------------------------------------------------------------------
    |
    | Supported: "attachment", "base64"
    |
    */
    'method' => 'base64',
];
```

Note that it will increase the e-mail size, and that it won't be decoded by
some e-mail clients such as Gmail.

## Mixed embedding methods

If you want to use both inline attachment and Base64 depending on the image,
you can specify the embedding method as the `data-auto-embed` attribute value:

```html
<p>
    <img src="https://domain.com/logo.png" data-auto-embed="base64">
</p>
<p>
    <img src="https://domain.com/item.png" data-auto-embed="attachment">
</p>
```


## Embedding entities

You might want to embed images that don't actually exist in your filesystem
(stored in the database).

In that case, make the entities you want to embed implement the
`EmbeddableEntity` interface:

```php
namespace App\Models;

use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model implements EmbeddableEntity
{
    /**
     * @param  mixed  $id
     * @return Picture
     */
    public static function findEmbeddable($id)
    {
        return static::find($id);
    }

    /**
     * @return mixed
     */
    public function getRawContent()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return 'profile_'.$this->id.'.png';
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return 'image/png';
    }
}
```

Then, you can use the `embed:ClassName:id` syntax in your e-mail template:

```html
<p>
    <img src="embed:App\Models\Picture:123">
</p>
```


## Contributing
Please feel free to submit pull requests if you can improve or add any
features.

We are currently using PSR-2. This is easy to implement and check with the PHP
Coding Standards Fixer.

<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=QPDFT3UXS6PTL&lc=GB&item_name=laravel%2dmail%2dauto%2dembed&item_number=laravel%2dmail%2dauto%2dembed&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">
        <img alt="Donate with Paypal" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif"/></a>

