# cPanel Laravel SDK

A Laravel package for managing cPanel accounts through the WHM API, authenticated
with a WHM root/reseller username and password (the same approach WHMCS uses).

Current scope: **WHM account management** (create, suspend, unsuspend, terminate,
list, summarize, change package, change password, edit quota). The HTTP layer
(`WhmClient`) is generic, so more WHM functions — and a cPanel UAPI pass-through
for per-account operations like email/DNS/MySQL — can be added as thin modules
on top without changing the core.

## Installation

```bash
composer require shiwang/cpanel-laravel-sdk
```

The service provider and `Cpanel` facade are auto-discovered by Laravel.

Publish the config file:

```bash
php artisan vendor:publish --tag=cpanel-config
```

## Configuration

Add to your `.env`:

```
CPANEL_WHM_HOST=whm.yourserver.com
CPANEL_WHM_PORT=2087
CPANEL_WHM_USERNAME=root
CPANEL_WHM_PASSWORD=your-whm-password
CPANEL_WHM_VERIFY_SSL=true
CPANEL_WHM_TIMEOUT=30
```

> Consider using a dedicated reseller account with only the ACLs it needs,
> rather than the true `root` account, and keep the password out of version
> control.

## Usage

```php
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;

// Create an account
Cpanel::accounts()->create([
    'username' => 'newuser',
    'domain' => 'example.com',
    'password' => 'a-strong-password',
    'plan' => 'default',
]);

// Suspend / unsuspend
Cpanel::accounts()->suspend('newuser', 'Payment overdue');
Cpanel::accounts()->unsuspend('newuser');

// Terminate
Cpanel::accounts()->terminate('newuser');

// List / inspect
$accounts = Cpanel::accounts()->list(['search' => 'example.com', 'searchtype' => 'domain']);
$summary = Cpanel::accounts()->summary('newuser');

// Change package, password, quota
Cpanel::accounts()->changePackage('newuser', 'premium');
Cpanel::accounts()->changePassword('newuser', 'new-strong-password');
Cpanel::accounts()->editQuota('newuser', 5000); // MB, 0 = unlimited
```

### Raw WHM API access

Any WHM API 1 function not yet wrapped by a module can be called directly:

```php
Cpanel::whm()->request('listpkgs');
Cpanel::whm()->request('createacct', [...], 'POST');
```

### Error handling

Failed WHM calls (bad HTTP status, or `metadata.result === 0`) throw
`Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException`, which carries the
raw decoded response via `->response()`.

```php
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;

try {
    Cpanel::accounts()->create([...]);
} catch (WhmRequestException $e) {
    logger()->error($e->getMessage(), $e->response());
}
```

## Testing

```bash
composer install
./vendor/bin/phpunit
```

Tests use Orchestra Testbench and `Http::fake()` — no live WHM server required.

## Roadmap

- cPanel UAPI pass-through (via WHM's `cpanel` API function) for per-account
  email, DNS, MySQL, and SSL management without separate cPanel credentials.
- WHM package/plan management.
- DNS zone editor helpers.
