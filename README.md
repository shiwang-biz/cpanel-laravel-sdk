<p align="center">
  <img src="assets/logo.svg" width="140" height="140" alt="cPanel Laravel SDK logo">
</p>

<h1 align="center">cPanel Laravel SDK</h1>

<p align="center">
  <a href="https://packagist.org/packages/shiwang-biz/cpanel-laravel-sdk"><img src="https://img.shields.io/packagist/v/shiwang-biz/cpanel-laravel-sdk.svg?style=flat-square" alt="Latest Version on Packagist"></a>
  <a href="https://github.com/shiwang-biz/cpanel-laravel-sdk/releases"><img src="https://img.shields.io/github/v/release/shiwang-biz/cpanel-laravel-sdk?style=flat-square" alt="Latest Release"></a>
  <a href="https://packagist.org/packages/shiwang-biz/cpanel-laravel-sdk"><img src="https://img.shields.io/packagist/dt/shiwang-biz/cpanel-laravel-sdk.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/shiwang-biz/cpanel-laravel-sdk"><img src="https://img.shields.io/packagist/php-v/shiwang-biz/cpanel-laravel-sdk?style=flat-square" alt="PHP Version"></a>
  <img src="https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-FF2D20?style=flat-square&logo=laravel" alt="Laravel 10-13">
  <a href="LICENSE"><img src="https://img.shields.io/github/license/shiwang-biz/cpanel-laravel-sdk?style=flat-square" alt="License"></a>
</p>

A Laravel SDK for the WHM JSON API, authenticated via WHM root/reseller credentials (the same approach WHMCS uses to manage cPanel accounts without storing per-account passwords).

## Requirements

- PHP ^8.1
- Laravel (`illuminate/support`, `illuminate/http`) ^10.0 | ^11.0 | ^12.0 | ^13.0

## Installation

```bash
composer require shiwang-biz/cpanel-laravel-sdk
```

The package auto-registers its service provider and `Cpanel` facade via Laravel package discovery.

Publish the config file:

```bash
php artisan vendor:publish --tag=cpanel-config
```

## Configuration

Set the following in your `.env`:

```env
CPANEL_WHM_HOST=whm.example.com
CPANEL_WHM_PORT=2087
CPANEL_WHM_USERNAME=root
CPANEL_WHM_PASSWORD=your-whm-password
CPANEL_WHM_VERIFY_SSL=true
CPANEL_WHM_TIMEOUT=30
```

| Key | Env Variable | Default | Description |
| --- | --- | --- | --- |
| `host` | `CPANEL_WHM_HOST` | — | WHM server hostname |
| `port` | `CPANEL_WHM_PORT` | `2087` | WHM API port |
| `username` | `CPANEL_WHM_USERNAME` | — | WHM root/reseller username |
| `password` | `CPANEL_WHM_PASSWORD` | — | WHM root/reseller password |
| `verify_ssl` | `CPANEL_WHM_VERIFY_SSL` | `true` | Verify the WHM server's SSL certificate |
| `timeout` | `CPANEL_WHM_TIMEOUT` | `30` | HTTP request timeout in seconds |

## Usage

Use the `Cpanel` facade, or inject `Shiwang\CpanelLaravelSdk\CpanelManager`.

```php
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;

// Create a cPanel account
Cpanel::accounts()->create([
    'username' => 'newuser',
    'domain' => 'example.com',
    'password' => 'S3cur3Pass!',
    'plan' => 'default',
]);

// Suspend / unsuspend
Cpanel::accounts()->suspend('newuser', 'Non-payment');
Cpanel::accounts()->unsuspend('newuser');

// Terminate an account
Cpanel::accounts()->terminate('newuser', keepDns: false);

// List accounts (optionally filtered)
Cpanel::accounts()->list(['search' => 'example.com', 'searchtype' => 'domain']);

// Account summary
Cpanel::accounts()->summary('newuser');

// Change hosting package / password / quota
Cpanel::accounts()->changePackage('newuser', 'premium');
Cpanel::accounts()->changePassword('newuser', 'N3wPass!');
Cpanel::accounts()->editQuota('newuser', 5000); // MB, 0 = unlimited
```

```php
// Create a hosting package
Cpanel::packages()->create([
    'name' => 'gold',
    'quota' => 5000,
    'bwlimit' => 10000,
]);

// Update / delete a package
Cpanel::packages()->update('gold', ['quota' => 10000]);
Cpanel::packages()->delete('gold');

// List packages
Cpanel::packages()->list();
```

### Calling raw WHM API functions

Any WHM API 1 function not yet wrapped by this SDK can be called directly through the underlying client:

```php
Cpanel::whm()->request('showhostname', [], 'GET');
```

## Error Handling

Failed HTTP requests and unsuccessful WHM API results (`metadata.result !== 1` or legacy `result[0].status !== 1`) both throw `Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException`, which exposes the decoded response and HTTP status:

```php
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;

try {
    Cpanel::accounts()->suspend('ghost');
} catch (WhmRequestException $e) {
    $e->getMessage();  // "WHM API call [suspendacct] failed: No such user"
    $e->response();    // full decoded WHM response
    $e->httpStatus();  // HTTP status code
}
```

## Modules

| Module | Access | Description |
| --- | --- | --- |
| `AccountManager` | `Cpanel::accounts()` | Create, suspend, unsuspend, terminate, list, and manage cPanel accounts |
| `PackageManager` | `Cpanel::packages()` | Create, update, delete, and list hosting packages/plans |

More modules will be added over time. `Cpanel::whm()` is always available as an escape hatch for any WHM API function not yet wrapped.

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT
