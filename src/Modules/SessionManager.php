<?php

namespace Shiwang\CpanelLaravelSdk\Modules;

use Shiwang\CpanelLaravelSdk\WhmClient;

/**
 * Creates one-time login sessions for cPanel/Webmail/WHM — the mechanism
 * behind WHMCS-style "Log in to cPanel" buttons. No account password is
 * needed; WHM issues a short-lived session URL for the target service.
 */
class SessionManager
{
    public function __construct(protected WhmClient $client)
    {
    }

    /**
     * @param  string  $service  'cpaneld', 'webmaild', or 'whostmgrd'.
     * @param  string|null  $app  Optional target app within the service, e.g. 'roundcube'.
     * @return array<string, mixed>
     */
    public function create(string $username, string $service = 'cpaneld', ?string $app = null, ?string $locale = null): array
    {
        return $this->client->request('create_user_session', array_filter([
            'user' => $username,
            'service' => $service,
            'app' => $app,
            'locale' => $locale,
        ]), 'POST');
    }

    /**
     * One-time login URL for the cPanel dashboard.
     */
    public function cpanelLoginUrl(string $username, ?string $app = null, ?string $locale = null): string
    {
        return $this->create($username, 'cpaneld', $app, $locale)['data']['url'];
    }

    /**
     * One-time login URL for Webmail.
     */
    public function webmailLoginUrl(string $username, ?string $app = null, ?string $locale = null): string
    {
        return $this->create($username, 'webmaild', $app, $locale)['data']['url'];
    }

    /**
     * One-time login URL for the WHM dashboard. Requires a WHM root/reseller
     * username, not a regular cPanel account username.
     */
    public function whmLoginUrl(string $username, ?string $app = null, ?string $locale = null): string
    {
        return $this->create($username, 'whostmgrd', $app, $locale)['data']['url'];
    }
}
