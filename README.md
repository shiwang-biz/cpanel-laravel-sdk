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

```php
// Create a DNS zone
Cpanel::dns()->create('example.com', '192.0.2.10');

// Dump / reset / delete a zone
Cpanel::dns()->dump('example.com');
Cpanel::dns()->reset('example.com');
Cpanel::dns()->delete('example.com');

// Edit zone records (raw WHM editzone params, e.g. add/edit/remove records)
Cpanel::dns()->editZone('example.com', [
    'add' => [
        ['record' => 'www', 'type' => 'A', 'ttl' => 14400, 'data' => '192.0.2.10'],
    ],
]);
```

```php
// Install an SSL certificate
Cpanel::ssl()->install('example.com', $certPem, $keyPem, $caBundlePem);

// Fetch installed certificate info
Cpanel::ssl()->info('example.com');

// Trigger an AutoSSL check/renewal for a cPanel user
Cpanel::ssl()->runAutoSsl('newuser');
```

```php
// Addon domains, subdomains, and parked domains have no direct WHM API 1
// equivalent — WHM proxies these through the target cPanel account's classic
// API 2 modules, so every call needs that account's cPanel username.
Cpanel::domains()->addAddonDomain('newuser', 'addon.com', 'addon', 'public_html/addon');
Cpanel::domains()->deleteAddonDomain('newuser', 'addon.com', 'addon');

Cpanel::domains()->addSubdomain('newuser', 'blog', 'example.com', 'public_html/blog');
Cpanel::domains()->deleteSubdomain('newuser', 'blog.example.com');
Cpanel::domains()->listSubdomains('newuser');

Cpanel::domains()->parkDomain('newuser', 'parked.com', 'example.com');
Cpanel::domains()->unparkDomain('newuser', 'parked.com', 'example.com');
Cpanel::domains()->listParkedDomains('newuser');
```

```php
// Email accounts are also account-scoped and proxied the same way as DomainManager.
Cpanel::email()->create('newuser', 'example.com', 'info', 'S3cur3Pass!', 500); // quota in MB, 0 = unlimited
Cpanel::email()->delete('newuser', 'example.com', 'info');
Cpanel::email()->changePassword('newuser', 'example.com', 'info', 'N3wPass!');
Cpanel::email()->editQuota('newuser', 'example.com', 'info', 1000);
Cpanel::email()->list('newuser', 'example.com'); // domain filter optional
```

```php
// One-time login URLs for cPanel/Webmail — no account password needed
// (the mechanism behind WHMCS-style "Log in to cPanel" buttons).
$cpanelUrl = Cpanel::sessions()->cpanelLoginUrl('newuser');
$webmailUrl = Cpanel::sessions()->webmailLoginUrl('newuser', app: 'roundcube');

// Or the raw call, for the WHM dashboard itself (service: whostmgrd) etc.
Cpanel::sessions()->create('newuser', service: 'cpaneld', locale: 'en');
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
| `DnsManager` | `Cpanel::dns()` | Create, delete, dump, reset DNS zones, and edit zone records |
| `SslManager` | `Cpanel::ssl()` | Install SSL certificates, fetch certificate info, and trigger AutoSSL runs |
| `DomainManager` | `Cpanel::domains()` | Add/remove addon domains, subdomains, and parked domains on an account |
| `EmailManager` | `Cpanel::email()` | Create, delete, list, and manage email accounts (mailboxes) on an account |
| `SessionManager` | `Cpanel::sessions()` | Create one-time login sessions/URLs for cPanel, Webmail, or WHM |

More modules will be added over time. `Cpanel::whm()` is always available as an escape hatch for any WHM API function not yet wrapped.

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT
