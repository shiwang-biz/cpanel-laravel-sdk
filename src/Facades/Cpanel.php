<?php

namespace Shiwang\CpanelLaravelSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Shiwang\CpanelLaravelSdk\CpanelManager;
use Shiwang\CpanelLaravelSdk\Modules\AccountManager;
use Shiwang\CpanelLaravelSdk\Modules\PackageManager;
use Shiwang\CpanelLaravelSdk\WhmClient;

/**
 * @method static WhmClient whm()
 * @method static AccountManager accounts()
 * @method static PackageManager packages()
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
