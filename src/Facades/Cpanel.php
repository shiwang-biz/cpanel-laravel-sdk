<?php

namespace Shiwang\CpanelLaravelSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Shiwang\CpanelLaravelSdk\CpanelManager;
use Shiwang\CpanelLaravelSdk\Modules\AccountManager;
use Shiwang\CpanelLaravelSdk\Modules\DnsManager;
use Shiwang\CpanelLaravelSdk\Modules\DomainManager;
use Shiwang\CpanelLaravelSdk\Modules\EmailManager;
use Shiwang\CpanelLaravelSdk\Modules\PackageManager;
use Shiwang\CpanelLaravelSdk\Modules\SslManager;
use Shiwang\CpanelLaravelSdk\WhmClient;

/**
 * @method static WhmClient whm()
 * @method static AccountManager accounts()
 * @method static PackageManager packages()
 * @method static DnsManager dns()
 * @method static SslManager ssl()
 * @method static DomainManager domains()
 * @method static EmailManager email()
 *
 * @see CpanelManager
 */
class Cpanel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CpanelManager::class;
    }
}
