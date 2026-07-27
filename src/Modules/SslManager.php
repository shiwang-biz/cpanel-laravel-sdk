<?php

namespace Shiwang\CpanelLaravelSdk\Modules;

use Shiwang\CpanelLaravelSdk\WhmClient;

class SslManager
{
    public function __construct(protected WhmClient $client)
    {
    }

    /**
     * Install an SSL certificate on a domain.
     *
     * @return array<string, mixed>
     */
    public function install(string $domain, string $cert, string $key, ?string $caBundle = null): array
    {
        return $this->client->request('installssl', array_filter([
            'domain' => $domain,
            'crt' => $cert,
            'key' => $key,
            'cabundle' => $caBundle,
        ]), 'POST');
    }

    /**
     * Fetch the installed SSL certificate's details for a domain.
     *
     * @return array<string, mixed>
     */
    public function info(string $domain): array
    {
        return $this->client->request('fetchsslinfo', [
            'domain' => $domain,
        ]);
    }

    /**
     * Trigger an AutoSSL check/renewal run for a cPanel user.
     *
     * @return array<string, mixed>
     */
    public function runAutoSsl(string $username): array
    {
        return $this->client->request('start_autossl_check', [
            'user' => $username,
        ], 'POST');
    }
}
