[![Actions Status](https://github.com/rsvpify/laravel-mail-auto-embed/workflows/Testing%20Laravel%20Package/badge.svg)](https://github.com/rsvpify/laravel-mail-auto-embed/actions)

# Laravel Mail Auto Embed


## Install

You can install the package via composer:
```bash
$ composer require rsvpify/laravel-mail-auto-embed
```

This package uses Laravel 5.5 Package Auto-Discovery.<br>
For previous versions of Laravel, you need to add the following Service Provider:

```php
$providers = [
    ...
    \Rsvpify\LaravelMailAutoEmbed\ServiceProvider::class,
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

When sending, it will replace the link that would normally be generated:
> `<img src="https://domain.com/products/product-1.png">` 

by an embedded inline attachment of the image:
> `<img src="cid:3991f143cf1a86257f8671883736613c@Swift.generated">`.

It also works for raw html too:

```html
// eg: resources/vendor/mail/html/header.blade.php
<tr>
    <td class="header">
        <a href="{{ $url }}">
            <img src="https://domain.com/logo.png" class="img-header">
        </a>
    </td>
</tr>
```

If you do not want to use automatic embedding for specific images (because they are hosted elsewhere, if you want to use some kind of image tracker, etc.) 
simply add the attribute `data-skip-embed` in the image tag:

```html
<img src="https://domain.com/logo.png" data-skip-embed class="img-header">
```
### Local resources

For local resources that are not available publicly, use `file://` urls, example

```html
<img src="file://{{ resource_path('assets/img/logo.png') }}" alt="Logo" border="0"/>
```

## Configuration

The defaults are set in `config/mail-auto-embed.php`. You can copy this file to your own config directory to modify the values using this command:

```shell
php artisan vendor:publish --provider="Rsvpify\LaravelMailAutoEmbed\ServiceProvider"
```

### Explicit embedding configuration

By default, images are embedded automatically, unless you add the `data-skip-embed` attribute.

You can also disable auto-embedding globally by setting the `MAIL_AUTO_EMBED` environment variable to `false`.
You can then enable embedding for specific images with the `data-auto-embed` attribute.

```
# .env
MAIL_AUTO_EMBED=false
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

If you prefer to use Base64 instead of inline attachments, you can do so by setting the `MAIL_AUTO_EMBED_METHOD` environment variable to `base64`. 

Note that it will increase the e-mail size, and that it won't be decoded by some e-mail clients such as Gmail.


## Mixed embedding methods

If you want to use both inline attachment and Base64 depending on the image, you can specify the embedding method as the `data-auto-embed` attribute value:   

```html
<p>
    <img src="https://domain.com/logo.png" data-auto-embed="base64">
</p>
<p>
    <img src="https://domain.com/item.png" data-auto-embed="attachment">
</p>
```


## Embedding entities

You might want to embed images that don't actually exist in your filesystem (stored in the database).

In that case, make the entities you want to embed implement the `EmbeddableEntity` interface:

```php
namespace App\Models;

use Rsvpify\LaravelMailAutoEmbed\Models\EmbeddableEntity;
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
Please feel free to submit pull requests if you can improve or add any features.

We are currently using PSR-2. This is easy to implement and check with the PHP Coding Standards Fixer.

<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=QPDFT3UXS6PTL&lc=GB&item_name=laravel%2dmail%2dauto%2dembed&item_number=laravel%2dmail%2dauto%2dembed&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">
        <img alt="Donate with Paypal" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif"/></a>

