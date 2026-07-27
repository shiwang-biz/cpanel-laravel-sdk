<?php

namespace Shiwang\CpanelLaravelSdk;

use Shiwang\CpanelLaravelSdk\Modules\AccountManager;
use Shiwang\CpanelLaravelSdk\Modules\PackageManager;

class CpanelManager
{
    protected ?AccountManager $accounts = null;

    protected ?PackageManager $packages = null;

    public function __construct(protected WhmClient $whm)
    {
    }

    /**
     * Access the raw WHM API client for calls not yet wrapped by this SDK.
     */
    public function whm(): WhmClient
    {
        return $this->whm;
    }

    public function accounts(): AccountManager
    {
        return $this->accounts ??= new AccountManager($this->whm);
    }

    public function packages(): PackageManager
    {
        return $this->packages ??= new PackageManager($this->whm);
    }
}
