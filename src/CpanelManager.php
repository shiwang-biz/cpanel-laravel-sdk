<?php

namespace Shiwang\CpanelLaravelSdk;

use Shiwang\CpanelLaravelSdk\Modules\AccountManager;
use Shiwang\CpanelLaravelSdk\Modules\DnsManager;
use Shiwang\CpanelLaravelSdk\Modules\DomainManager;
use Shiwang\CpanelLaravelSdk\Modules\EmailManager;
use Shiwang\CpanelLaravelSdk\Modules\PackageManager;
use Shiwang\CpanelLaravelSdk\Modules\SslManager;

class CpanelManager
{
    protected ?AccountManager $accounts = null;

    protected ?PackageManager $packages = null;

    protected ?DnsManager $dns = null;

    protected ?SslManager $ssl = null;

    protected ?DomainManager $domains = null;

    protected ?EmailManager $email = null;

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

    public function dns(): DnsManager
    {
        return $this->dns ??= new DnsManager($this->whm);
    }

    public function ssl(): SslManager
    {
        return $this->ssl ??= new SslManager($this->whm);
    }

    public function domains(): DomainManager
    {
        return $this->domains ??= new DomainManager($this->whm);
    }

    public function email(): EmailManager
    {
        return $this->email ??= new EmailManager($this->whm);
    }
}
